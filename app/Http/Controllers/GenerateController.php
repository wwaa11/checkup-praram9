<?php
namespace App\Http\Controllers;

use App\Models\PatientMaster;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GenerateController extends Controller
{
    public function index()
    {

        return view('verify.index');
    }

    private function setStationCode($code)
    {
        switch ($code) {
            case '01':
                return 'วัดความดัน';
            case '011':
                return 'ห้องเจาะเลือด';
            case '012':
                return 'ตรวจตา';
            case '013':
                return 'ตรวจทันตกรรม';
            case '02':
                return 'EKG';
            case '03':
                return 'ABI';
            case '04':
                return 'EST|Echo';
            case '05':
                return 'Echo|Echo';
            case '06':
                return 'Xray';
            case '07':
                return 'US';
            case '08':
                return 'Mammogram';
            case '09':
                return 'BoneDense';
            case '10':
                return 'OBS';
            case '11':
                return 'CT UpperGI';
            case '12':
                return 'MRI';
            case '13':
                return 'หมอตา';
            default:
                return 'ไม่ระบุ';
        }
    }

    public function search(Request $request)
    {
        $request->validate([
            'input' => 'required',
        ]);

        $input = $request->input('input');
        $data  = [
            'status'  => 'failed',
            'message' => 'Input is required',
        ];
        $postData = [
            'hn'         => ($request->type == 'hn') ? $input : '',
            'nationalid' => ($request->type == 'nationalid') ? $input : '',
            'passport'   => ($request->type == 'passport') ? $input : '',
            'phoneno'    => ($request->type == 'phoneno') ? $input : '',
        ];

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . env('Token_PatientData'),
        ])
            ->withoutVerifying()
            ->post('https://172.20.10.200:8001/hisapi/easyqpatientdata', $postData);

        if ($response->successful()) {
            $dataJson = $response->json();
            $data     = [
                'status'  => 'success',
                'message' => 'Search patient successfully',
                'patient' => [],
            ];
            if ($dataJson['data'] != null) {
                foreach ($dataJson['data'] as $key => $value) {
                    $patient = PatientMaster::where('hn', $value['hn'])->whereDate('date', date('Y-m-d'))->first();
                    if ($patient) {
                        $number = $patient->prevn->number ?? 'Generating...';
                    }
                    session()->put($value['hn'], $value);
                    $data['patient'][] = [
                        'isExist'     => $patient ? true : false,
                        'hn'          => $value['hn'],
                        'firstname'   => $value['firstname'],
                        'lastname'    => $value['lastname'],
                        'nationality' => $value['nationality'],
                        'number'      => $number ?? null,
                    ];
                }
            } else {
                $patient = PatientMaster::where('hn', $input)->first();
                if ($patient) {
                    $number = $patient->prevn->number ?? 'Generating...';
                }
                $data['patient'][] = [
                    'isExist'     => $patient ? true : false,
                    'hn'          => $input,
                    'firstname'   => 'New Patient',
                    'lastname'    => '',
                    'nationality' => 'Thai',
                    'number'      => $number ?? null,
                ];
                session()->put($input, [
                    'hn'          => $input,
                    'firstname'   => 'New Patient',
                    'lastname'    => '',
                    'nationality' => 'Thai',
                ]);
            }
        } else {
            $data = [
                'status'  => 'failed',
                'message' => 'Unexpected HTTP status: ' . $response->status() . ' ' . $response->reason(),
            ];
        }

        return response()->json($data);
    }

    public function getNumber(Request $request)
    {
        $request->validate([
            'hn' => 'required',
        ]);
        $hn   = $request->hn;
        $data = [
            'status'  => 'failed',
            'message' => 'Input is required',
        ];
        $postData = [
            "hn"          => $hn,
            "aptdate"     => date('Y-m-d'),
            "isautogenvn" => false,
        ];

        $patient     = PatientMaster::where('hn', $hn)->first();
        $patientData = session()->get($hn);
        if (! $patient) {
            $patient          = new PatientMaster();
            $patient->date    = date('Y-m-d');
            $patient->hn      = $patientData['hn'];
            $patient->name    = $patientData['firstname'] . ' ' . $patientData['lastname'];
            $patient->english = ($patientData['nationality'] == 'Thai') ? false : true;
            $patient->save();
        }

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . env('Token_VisitNumber'),
        ])
            ->withoutVerifying()
            ->post('https://172.20.10.200:8001/easyqpatientapt', $postData);

        if ($response->successful()) {
            $preVN = $patient->prevn()->firstOrCreate([
                'date'    => date('Y-m-d'),
                'type'    => 'A',
                'checkin' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $preVN = $patient->prevn()->firstOrCreate([
                'date'    => date('Y-m-d'),
                'type'    => 'M',
                'status'  => 'walkin',
                'checkin' => date('Y-m-d H:i:s'),
            ]);
        }

        $data = [
            'status'  => 'success',
            'message' => 'Generating...',
            'number'  => $preVN->number ?? 'Generating...',
        ];

        return response()->json($data);
    }

    public function newCheckUp(Request $request)
    {
        $token = $request->headers->get('Authorization');
        $request->headers->set('Accept', 'application/json');
        if (env('Token_NewCheckUp') !== str_replace('Bearer ', '', $token)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Authorization token is invalid',
            ], 401);
        }

        $validatedData = $request->validate([
            'visitdate' => 'required|date',
            'vn'        => 'required',
            'hn'        => 'required',
            'stations'  => 'required|array',
        ], [
            'visitdate.required' => 'Please provide the Visit Date.',
            'visitdate.date'     => 'Visit Date must be a valid date.',
            'vn.required'        => 'Please provide the Visit Number (VN).',
            'hn.required'        => 'Please provide the Hospital Number (HN).',
            'stations.required'  => 'Stations is required',
            'stations.array'     => 'Stations must be an array',
        ]);

        $patient = PatientMaster::whereDate('date', $validatedData['visitdate'])->where('vn', $validatedData['vn'])->first();
        if ($patient == null) {
            $patientInfo = DB::connection('monkey')
                ->table('apipatientinfo')
                ->where('hn', $validatedData['hn'])
                ->select(
                    'fistnameth',
                    'lastnameth',
                    'firstnameen',
                    'lastnameen',
                    'nationality',
                )
                ->first();
            if (! $patientInfo) {

                return response()->json([
                    'status'  => 'warning',
                    'message' => 'Not found Patient data.',
                ], 404);
            }

            $perferEnglish = ($patientInfo->nationality == 'Thai') ? false : true;
            if ($perferEnglish) {
                $name = $patientInfo->firstnameen . ' ' . $patientInfo->lastnameen;
            } else {
                $name = $patientInfo->fistnameth . ' ' . $patientInfo->lastnameth;
            }

            $patient          = new PatientMaster();
            $patient->date    = $validatedData['visitdate'];
            $patient->vn      = $validatedData['vn'];
            $patient->hn      = $validatedData['hn'];
            $patient->name    = $name;
            $patient->english = $perferEnglish;
            $patient->save();

            $patient->logs()->create([
                'patient' => $patient->id,
                'detail'  => 'Create Patient',
                'user'    => 'API',
            ]);
        }

        foreach ($validatedData['stations'] as $station) {
            switch ($station) {
                case '01':
                    $code = 'vs';
                    break;
                case '011':
                    $code = 'lab';
                    break;
                case '02':
                    $code = 'ekg';
                    break;
                case '03':
                    $code = 'abi';
                    break;
                case '04':
                    $code = 'echo';
                    break;
                case '05':
                    $code = 'echo';
                    break;
                case '06':
                    $code = 'xray';
                    break;
                case '07':
                    $code = 'ultrasound';
                    break;
                case '08':
                    $code = 'mammogram';
                    break;
                case '09':
                    $code = 'bonedensity';
                    break;
                case '10':
                    $code = 'obs';
                    break;
                case '99':
                    $code = 'doctor';
                    break;
                default:
                    $code = $station;
                    break;
            }

            $exist = $patient->tasks()->where('code', $code)->first();
            if ($exist == null) {
                $patient->tasks()->create([
                    'date'   => $validatedData['visitdate'],
                    'code'   => $code,
                    'status' => ($code == 'vs') ? 'queue' : 'wait',
                ]);

                $patient->logs()->create([
                    'patient' => $patient->id,
                    'detail'  => 'Add CheckUp list ' . $this->setStationCode($station),
                    'user'    => 'API',
                ]);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Create patient task success',
        ]);
    }
}

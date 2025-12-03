<?php
namespace App\Http\Controllers;

use App\Models\PatientMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VerifyController extends Controller
{
    public function index()
    {

        return view('verify.index');
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
                        $number = $patient->getPreVN->number ?? 'Generating...';
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
                    $number = $patient->getPreVN->number ?? 'Generating...';
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
            $preVN = $patient->getPreVN()->firstOrCreate([
                'date'    => date('Y-m-d'),
                'type'    => 'A',
                'checkin' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $preVN = $patient->getPreVN()->firstOrCreate([
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
}

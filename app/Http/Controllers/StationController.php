<?php
namespace App\Http\Controllers;

use App\Events\RegisterChannelEvent;
use App\Models\PatientLog;
use App\Models\PatientPreVN;
use App\Models\PatientTask;
use App\Models\Room;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::where('enabled', true)->get();

        return view('stations.index')->with(compact('stations'));
    }

    public function room($roomID)
    {
        $room = Room::where('id', $roomID)->first();
        if (! $room) {
            return redirect()->route('stations.index')->with('error', 'Room not found');
        }

        switch ($room->station->name) {
            case 'Register':
                $view = 'stations.register.index';
                break;
            case 'Vitalsign':
                $view = 'stations.vitalsign.index';
                break;
            default:
                $view = 'stations.index';
                break;
        }

        if ($room->now != '-') {
            $now = ($room->station->name == 'Register') ? PatientPreVN::whereDate('date', date('Y-m-d'))->where('number', $room->now)->first() : PatientTask::whereDate('date', date('Y-m-d'))->where('number', $room->now)->first();
            if (! $now) {
                $room->now = '-';
                $room->save();
            }
        } else {
            $now = null;
        }

        return view($view)->with(compact('room', 'now'));
    }

    public function RegisterList()
    {
        $list = PatientPreVN::whereDate('date', date('Y-m-d'))->whereNotNull('number')->get();
        $data = [
            'followup' => [],
            'wait'     => [],
            'hold'     => [],
            'walkin'   => [],
        ];
        foreach ($list as $item) {
            if ($item->status == 'working') {
                continue;
            } else if ($item->status == 'followup') {
                $case = 'followup';
            } elseif ($item->status == 'hold') {
                $case = 'hold';
            } elseif ($item->status == 'walkin') {
                $case = 'walkin';
            } else {
                $case = 'wait';
            }

            $waiting_time = $item->checkin ? $item->checkin->diffInMinutes(now()) : 0;

            $data[$case][] = [
                'id'             => $item->id,
                'number'         => $item->number,
                'vn'             => $item->patient_master->vn,
                'hn'             => $item->patient_master->hn,
                'name'           => $item->patient_master->name,
                'prefer_english' => $item->patient_master->english,
                'checkin'        => $item->checkin,
                'waiting_time'   => sprintf("%02d", $waiting_time),
                'note'           => $item->note,
            ];
        }
        foreach ($data as $key => $value) {
            $data[$key] = collect($value)->sortBy('priority')->values()->all();
        }

        return response()->json($data);
    }

    public function RegisterCall(Request $request)
    {
        $roomID = $request->input('roomID');
        $id     = $request->input('id');

        $room = Room::where('id', $roomID)->first();
        if ($id == 'auto') {
            $prevns = PatientPreVN::whereDate('date', date('Y-m-d'))
                ->whereNull('call')
                ->get();
            $sortedData = $prevns->sortBy('priority', SORT_REGULAR, $descending = false);
            $prevn      = $sortedData->first();
        } else {
            $prevn = PatientPreVN::where('id', $id)->first();
        }

        if (! $room || ! $prevn) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No patient in queue',
            ]);
        }
        if ($prevn->status == 'working') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Patient already in working status',
            ]);
        }

        $room->now     = $prevn->number;
        $room->english = $prevn->patient_master->english;
        $room->save();

        $prevn->status = 'working';
        $prevn->call   = date('Y-m-d H:i:s');
        $prevn->save();

        RegisterChannelEvent::dispatch($room->station->code, $prevn->number, $room->name, $prevn->patient_master->english);

        $log          = new PatientLog;
        $log->patient = $prevn->patient_master->id;
        $log->detail  = 'เรียกคิว ' . $prevn->number . ' ที่ Counter ' . $room->name;
        $log->user    = auth()->user()->userid;
        $log->save();

        $response = [
            'status'  => 'success',
            'message' => 'Patient called successfully',
        ];

        return response()->json($response);
    }

    public function RegisterHold(Request $request)
    {
        $roomID = $request->input('roomID');
        $id     = $request->input('id');
        $reason = $request->input('reason');

        $room = Room::where('id', $roomID)->first();

        if ($id == 'auto') {
            $prevn = PatientPreVN::whereDate('date', date('Y-m-d'))
                ->where('number', $room->now)
                ->first();
        } else {
            $prevn = PatientPreVN::where('id', $id)->first();
        }

        if (! $prevn || ! $prevn) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Patient not found',
            ]);
        }

        $room->now = '-';
        $room->save();

        $prevn->status = 'hold';
        $prevn->note   = $reason;
        $prevn->save();

        $log          = new PatientLog;
        $log->patient = $prevn->patient_master->id;
        $log->detail  = 'หยุดคิว ' . $prevn->number;
        $log->user    = auth()->user()->userid;
        $log->save();

        $response = [
            'status'  => 'success',
            'message' => 'Patient hold successfully',
        ];

        return response()->json($response);
    }

    public function CallAgain(Request $request)
    {
        $roomID = $request->input('roomID');

        $room  = Room::find($roomID);
        $prevn = PatientPreVN::whereDate('date', date('Y-m-d'))
            ->where('number', $room->now)
            ->get();

        $response = [
            'status'  => 'success',
            'message' => 'Patient called successfully',
        ];

        return response()->json($response);
    }
}

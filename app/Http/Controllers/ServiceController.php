<?php
namespace App\Http\Controllers;

use App\Jobs\GenerateNumber;
use App\Models\PatientPreVN;
use App\Models\PatientTask;
use App\Models\Station;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function test()
    {

    }

    public function index()
    {

        return view('service.index');
    }

    public function dispatchGenerateNumber()
    {
        GenerateNumber::dispatch();
    }

    public function getDisplay(Request $request)
    {
        $stationsList = $request->input('stations');
        $stations     = Station::whereIn('code', $stationsList)->get();
        if ($request->prevn) {
            $current_tasks = PatientPreVN::whereDate('date', now())
                ->where('status', 'hold')
                ->orderBy('call', 'asc')
                ->get()
                ->sortBy('priority')
                ->values();
        } else {
            $current_tasks = PatientTask::whereDate('date', now())->whereIn('code', $stationsList)->get();
        }

        $servingList  = [];
        $waitingList  = [];
        $holdoingList = [];
        foreach ($stations as $station) {
            foreach ($station->rooms as $room) {
                $servingList[$room->station->code][] = [
                    'room'    => $room->room,
                    'now'     => $room->now,
                    'doctor'  => $room->doctor,
                    'english' => $room->english,
                ];
            }
        }

        foreach ($current_tasks as $task) {
            if ($request->prevn) {
                $holdoingList['register'][] = $task->number;
            } elseif ($task->status == 'queue') {
                $waitingList[$task->code][] = $task->patient_master->vn;
            } elseif ($task->status == 'hold') {
                $waitingList[$task->code][] = $task->patient_master->vn;
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'servingList'  => $servingList,
                'waitingList'  => $waitingList,
                'holdoingList' => $holdoingList,
            ],
        ]);
    }
}

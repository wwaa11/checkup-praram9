<?php
namespace App\Http\Controllers;

use App\Events\RegisterChannelEvent;
use App\Models\PatientMaster;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function test()
    {
        $number        = "M001";
        $counter       = "1";
        $language_code = "th";

        RegisterChannelEvent::dispatch($number, $counter, $language_code);

        dump('event sent');
    }

    public function history(Request $request)
    {
        $patient_master = null;
        $patient_master = PatientMaster::whereDate('date', date('Y-m-d'))->where('hn', $request->search)->first();
        if (! $patient_master) {
            $patient_master = PatientMaster::whereDate('date', date('Y-m-d'))->whereNotNull('vn')->where('vn', $request->search)->first();
        }

        return view("admin.history", compact('patient_master', 'request'));
    }
}

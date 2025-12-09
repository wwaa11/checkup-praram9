<?php
namespace App\Http\Controllers;

use App\Models\PatientMaster;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function test()
    {

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

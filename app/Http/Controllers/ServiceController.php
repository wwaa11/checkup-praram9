<?php
namespace App\Http\Controllers;

use App\Jobs\GenerateNumber;

class ServiceController extends Controller
{
    public function index()
    {

        return view('service.index');
    }
    public function dispatchGenerateNumber()
    {

        GenerateNumber::dispatch()->onQueue('generate-number');
    }
}

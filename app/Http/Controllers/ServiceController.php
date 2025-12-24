<?php
namespace App\Http\Controllers;

use App\Events\RegisterChannelEvent;
use App\Jobs\GenerateNumber;

class ServiceController extends Controller
{
    public function test()
    {
        RegisterChannelEvent::dispatch('test', 'room number', 'th');
    }

    public function index()
    {

        return view('service.index');
    }
    public function dispatchGenerateNumber()
    {

        GenerateNumber::dispatch()->onQueue('generate-number');
    }
}

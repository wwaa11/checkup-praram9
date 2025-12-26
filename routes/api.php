<?php

use App\Http\Controllers\GenerateController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::post('/new-checkup', [GenerateController::class, 'newCheckUp']);

Route::post('/get-queue', [ServiceController::class, 'getDisplay']);

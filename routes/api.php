<?php

use App\Http\Controllers\GenerateController;
use Illuminate\Support\Facades\Route;

Route::post('/new-checkup', [GenerateController::class, 'newCheckUp']);

<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'Login'])->name('login');

Route::get('/login', [AuthController::class, 'Login'])->name('login');
Route::post('/login', [AuthController::class, 'LoginRequest'])->name('post.login');
Route::post('/logout', [AuthController::class, 'LogoutRequest'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/statins/index', [StationController::class, 'index'])->name('stations.index');
    Route::get('/statins/room/{roomID}', [StationController::class, 'room'])->name('stations.room');

    Route::get('/register/list', [StationController::class, 'RegisterList'])->name('stations.register.list');
    Route::post('/register/call', [StationController::class, 'RegisterCall'])->name('stations.register.call');
    Route::post('/register/hold', [StationController::class, 'RegisterHold'])->name('stations.register.hold');
    Route::post('/register/success', [StationController::class, 'RegisterSuccess'])->name('stations.register.success');
    Route::post('/register/delete', [StationController::class, 'RegisterDelete'])->name('stations.register.delete');

    Route::get('/verify', [GenerateController::class, 'index'])->name('verify.index');
    Route::post('/verify/search', [GenerateController::class, 'search'])->name('verify.search');
    Route::post('/verify/getnumber', [GenerateController::class, 'getNumber'])->name('verify.getnumber');

    Route::get('/service/index', [ServiceController::class, 'index'])->name('service.index');
    Route::get('/service/generate-number', [ServiceController::class, 'dispatchGenerateNumber'])->name('service.generate-number');

    Route::get('/admin/history', [AdminController::class, 'history'])->name('admin.history');
});

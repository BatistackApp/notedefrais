<?php

use App\Http\Controllers\BridgeController;
use App\Services\ErrorHandlerToJiraService;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/bridge/connect', [BridgeController::class, 'connect'])->name('bridge.connect');
    Route::get('/bridge/callback', [BridgeController::class, 'callback'])->name('bridge.callback');
});

require __DIR__.'/settings.php';

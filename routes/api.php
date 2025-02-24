<?php

use App\Http\Controllers\EasyPayController;
use App\Http\Controllers\SuperWalletzController;
use Illuminate\Support\Facades\Route;

//Pay routes
Route::post('easy-pay/pay', [EasyPayController::class, 'pay'])->name('easy-pay.pay');
Route::post('super-walletz/pay', [SuperWalletzController::class, 'pay'])->name('super-walletz.pay');

//Callback routes
Route::post('super-walletz/callback', [SuperWalletzController::class, 'callback'])->name('super-walletz.callback');

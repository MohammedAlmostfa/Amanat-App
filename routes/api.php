<?php

use Illuminate\Http\Request;
use App\Models\CustomerDebts;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDebtsController;
use App\Http\Controllers\FinancialReportController;

Route::post('/login', [AuthController::class, 'login']); // Handles user login

Route::post('resetPassword', [AuthController::class, 'resetPassword']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('customer', CustomerController::class);

Route::apiResource('customerdebt', CustomerDebtsController::class);

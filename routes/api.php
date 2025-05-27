<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDebtsController;
use App\Http\Controllers\FinancialReportController;

Route::post('/login', [AuthController::class, 'login']); // Handles user login

Route::post('resetPassword', [AuthController::class, 'resetPassword']);



Route::apiResource('customer', CustomerController::class);
Route::get('customer/{id}/debts', [CustomerController::class,'show' ]);

Route::get('customers/debt', [CustomerController::class,'getAllCustomersWithDebt' ]);
Route::apiResource('/debt', CustomerDebtsController::class);
Route::apiResource('/financialReport', FinancialReportController::class);

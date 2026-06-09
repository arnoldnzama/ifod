<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\JobTitleController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view');

    // Module 1 : gestion administrative du personnel
    Route::get('employees', [EmployeeController::class, 'index'])->middleware('permission:employees.view');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->middleware('permission:employees.view');
    Route::get('employees/{employee}/movements', [EmployeeController::class, 'movements'])->middleware('permission:employees.view');
    Route::post('employees', [EmployeeController::class, 'store'])->middleware('permission:employees.create');
    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.update');
    Route::post('employees/{employee}/archive', [EmployeeController::class, 'archive'])->middleware('permission:employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employees.delete');

    // Organisation : départements / services / fonctions
    Route::get('departments', [DepartmentController::class, 'index'])->middleware('permission:employees.view');
    Route::get('departments/{department}', [DepartmentController::class, 'show'])->middleware('permission:employees.view');
    Route::get('services', [ServiceController::class, 'index'])->middleware('permission:employees.view');
    Route::get('job-titles', [JobTitleController::class, 'index'])->middleware('permission:employees.view');

    Route::middleware('permission:organisation.manage')->group(function () {
        Route::apiResource('departments', DepartmentController::class)->except(['index', 'show']);
        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::apiResource('job-titles', JobTitleController::class)->except(['index', 'show'])
            ->parameters(['job-titles' => 'jobTitle']);
    });
});

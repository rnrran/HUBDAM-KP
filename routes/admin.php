<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // User Management Routes
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/edit', [UserManagementController::class, 'editIndex'])->name('users.edit.index');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::post('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::get('/generate-password', [UserManagementController::class, 'generatePassword'])->name('generate.password');
    
    // Payroll Management Routes
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
    Route::post('/payroll', [PayrollController::class, 'store'])->name('payroll.store');
    Route::get('/payroll/{payroll}/edit', [PayrollController::class, 'edit'])->name('payroll.edit');
    Route::put('/payroll/{payroll}', [PayrollController::class, 'update'])->name('payroll.update');
    Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
    Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::get('/payroll/user/{userId}', [PayrollController::class, 'userHistory'])->name('payroll.user');
    Route::get('/payroll/user/{userId}/chart/{timeRange?}', [PayrollController::class, 'getUserChartData'])->name('payroll.user.chart');
    Route::get('/payroll-users', [PayrollController::class, 'getUsers'])->name('payroll.users');
});
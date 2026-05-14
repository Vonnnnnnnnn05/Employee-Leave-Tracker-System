<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:employee')->prefix('employee')->name('employee.')->group(function () {
        Route::get('/requests', [LeaveRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [LeaveRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [LeaveRequestController::class, 'store'])->name('requests.store');
        Route::patch('/requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('requests.cancel');
    });

    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
        Route::get('/requests', [ManagerController::class, 'requests'])->name('requests');
        Route::patch('/requests/{leaveRequest}/decision', [ManagerController::class, 'decide'])->name('requests.decide');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
        Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employees.store');
        Route::patch('/employees/{user}', [AdminController::class, 'updateEmployee'])->name('employees.update');
        Route::get('/leave-types', [AdminController::class, 'leaveTypes'])->name('leave-types');
        Route::post('/leave-types', [AdminController::class, 'storeLeaveType'])->name('leave-types.store');
        Route::get('/balances', [AdminController::class, 'balances'])->name('balances');
        Route::post('/balances', [AdminController::class, 'updateBalance'])->name('balances.update');
        Route::get('/requests', [AdminController::class, 'requests'])->name('requests');
        Route::patch('/requests/{leaveRequest}/decision', [AdminController::class, 'decide'])->name('requests.decide');
        Route::patch('/requests/{leaveRequest}/cancel', [AdminController::class, 'cancelApproved'])->name('requests.cancel');
        Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');
        Route::post('/attendance', [AdminController::class, 'storeAttendance'])->name('attendance.store');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    });
});

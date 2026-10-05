<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');

    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance', [AttendanceController::class, 'store'])->middleware('role:teacher');
    Route::post('/progress', [ProgressController::class, 'update'])->middleware('role:teacher');

    Route::middleware('role:principal')->group(function () {
        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('/payments/reconcile', [PaymentController::class, 'reconcile'])->name('payments.reconcile');
        Route::post('/payments/non-billable-months', [PaymentController::class, 'storeNonBillableMonth'])->name('payments.non-billable.store');
        Route::delete('/payments/non-billable-months/{nonBillableMonth}', [PaymentController::class, 'destroyNonBillableMonth'])->name('payments.non-billable.destroy');
        Route::get('/payments/students/{student}', [PaymentController::class, 'show'])->name('payments.show');
        Route::patch('/payments/students/{student}/fee', [PaymentController::class, 'updateFee'])->name('payments.fee.update');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::patch('/subjects/{subject}/capacity', [SubjectController::class, 'updateCapacity'])
            ->name('subjects.capacity.update');

        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    });
});

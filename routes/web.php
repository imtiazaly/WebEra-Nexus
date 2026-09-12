<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\WeeklyReportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'auth/Login')->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('internships', InternshipController::class);

    Route::resource('students', StudentController::class);
    Route::post('students/{student}/assign-project', [StudentController::class, 'assignProject'])->name('students.assign-project');
    Route::post('students/{student}/weekly-reports', [WeeklyReportController::class, 'store'])->name('weekly-reports.store');
    Route::put('weekly-reports/{weeklyReport}', [WeeklyReportController::class, 'update'])->name('weekly-reports.update');
});

require __DIR__.'/settings.php';

<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\WeeklyReportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'auth/Login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    // Resource routes for clients, projects, internships, students, and weekly reports
    Route::resource('clients', ClientController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('internships', InternshipController::class);

    // In-context Service and Internship Track Management Routes
    Route::post('services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::post('services/{service}/toggle', [ServiceController::class, 'toggle'])->name('services.toggle');

    // Resource routes for students and weekly reports with additional custom routes
    Route::resource('students', StudentController::class);
    Route::post('students/{student}/assign-project', [StudentController::class, 'assignProject'])->name('students.assign-project');
    Route::post('students/{student}/weekly-reports', [WeeklyReportController::class, 'store'])->name('weekly-reports.store');
    Route::put('weekly-reports/{weeklyReport}', [WeeklyReportController::class, 'update'])->name('weekly-reports.update');
    Route::post('/students/{student}/enroll-batch', [StudentController::class, 'enrollBatch'])->name('students.enroll-batch');
    Route::post('/students/{student}/batches/{internship}/update', [StudentController::class, 'updateBatchEnrollment'])->name('students.batches.update');
    Route::post('/students/{student}/batches/{internship}/set-active', [StudentController::class, 'setActiveBatch'])->name('students.batches.set-active');
    Route::post('/students/{student}/batches/{internship}/graduate', [StudentController::class, 'graduateBatch'])->name('students.batches.graduate');

    // AI-related routes for generating briefs and summaries
    Route::post('/clients/{client}/ai-brief', [AiController::class, 'generateClientBrief'])->name('clients.ai-brief');
    Route::post('/weekly-reports/{report}/ai-summary', [AiController::class, 'generateReportSummary'])->name('weekly-reports.ai-summary');
    Route::post('/projects/{project}/ai-summary', [AiController::class, 'generateProjectSummary'])->name('projects.ai-summary');
});

require __DIR__.'/settings.php';

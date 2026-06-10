<?php

use App\Http\Controllers\AgendaScheduleController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('agenda-schedules/{agenda_schedule}/activities/create', [ActivityController::class, 'createFromSchedule'])
        ->name('agenda-schedules.activities.create');
    Route::post('agenda-schedules/{agenda_schedule}/activities', [ActivityController::class, 'storeFromSchedule'])
        ->name('agenda-schedules.activities.store');
    Route::patch('agenda-schedules/{agenda_schedule}/deactivate', [AgendaScheduleController::class, 'deactivate'])
        ->name('agenda-schedules.deactivate');
    Route::resource('agenda-schedules', AgendaScheduleController::class)->except('destroy');

    Route::patch('activities/{activity}/status', [ActivityController::class, 'updateStatus'])
        ->name('activities.status.update');
    Route::get('activities/{activity}/attendances', [ActivityController::class, 'attendancesPlaceholder'])
        ->name('activities.attendances.index');
    Route::resource('activities', ActivityController::class);

    Route::resource('members', MemberController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

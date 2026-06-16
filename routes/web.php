<?php

use App\Http\Controllers\AgendaScheduleController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityAttendanceQrController;
use App\Http\Controllers\ActivityAttendanceExportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCheckInController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberAccountController;
use App\Http\Controllers\MemberDashboardAttendanceController;
use App\Http\Controllers\MemberExportController;
use App\Http\Controllers\MemberHomeController;
use App\Http\Controllers\MemberImportTemplateController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->role === 'member'
        ? redirect()->route('member.home')
        : redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'internal'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/member', MemberHomeController::class)
        ->middleware('member')
        ->name('member.home');
    Route::post('/member/activities/{activity}/check-in', [MemberDashboardAttendanceController::class, 'store'])
        ->middleware('member')
        ->name('member.activities.check-in');

    Route::get('attendance/check-in/{token}', [AttendanceCheckInController::class, 'show'])
        ->name('attendance.check-in.show');
    Route::post('attendance/check-in/{token}', [AttendanceCheckInController::class, 'store'])
        ->name('attendance.check-in.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/member', [ProfileController::class, 'updateMember'])->name('profile.member.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'internal'])->group(function () {
    Route::get('attendance-reports', [AttendanceReportController::class, 'index'])
        ->name('attendance-reports.index');

    Route::get('settings', [SystemSettingController::class, 'edit'])
        ->name('settings.edit');
    Route::put('settings', [SystemSettingController::class, 'update'])
        ->name('settings.update');

    Route::get('agenda-schedules/{agenda_schedule}/activities/create', [ActivityController::class, 'createFromSchedule'])
        ->name('agenda-schedules.activities.create');
    Route::post('agenda-schedules/{agenda_schedule}/activities', [ActivityController::class, 'storeFromSchedule'])
        ->name('agenda-schedules.activities.store');
    Route::get('agenda-schedules/{agenda_schedule}/generate-monthly', [AgendaScheduleController::class, 'generateMonthlyForm'])
        ->name('agenda-schedules.generate-monthly.create');
    Route::post('agenda-schedules/{agenda_schedule}/generate-monthly', [AgendaScheduleController::class, 'generateMonthly'])
        ->name('agenda-schedules.generate-monthly.store');
    Route::patch('agenda-schedules/{agenda_schedule}/deactivate', [AgendaScheduleController::class, 'deactivate'])
        ->name('agenda-schedules.deactivate');
    Route::resource('agenda-schedules', AgendaScheduleController::class)->except('destroy');

    Route::patch('activities/{activity}/status', [ActivityController::class, 'updateStatus'])
        ->name('activities.status.update');
    Route::get('activities/{activity}/attendance-qr', [ActivityAttendanceQrController::class, 'show'])
        ->name('activities.attendance-qr');
    Route::get('activities/{activity}/attendances/export', ActivityAttendanceExportController::class)
        ->name('activities.attendances.export');
    Route::get('activities/{activity}/attendances', [AttendanceController::class, 'byActivity'])
        ->name('activities.attendances.index');
    Route::get('activities/{activity}/attendances/create', [AttendanceController::class, 'createManual'])
        ->name('activities.attendances.create');
    Route::post('activities/{activity}/attendances', [AttendanceController::class, 'storeManual'])
        ->name('activities.attendances.store');
    Route::get('activities/{activity}/attendances/bulk', [AttendanceController::class, 'createBulk'])
        ->name('activities.attendances.bulk.create');
    Route::put('activities/{activity}/attendances/bulk', [AttendanceController::class, 'storeBulk'])
        ->name('activities.attendances.bulk.store');
    Route::post('activities/{activity}/attendances/sync-participants', [AttendanceController::class, 'syncParticipants'])
        ->name('activities.attendances.sync-participants');
    Route::resource('activities', ActivityController::class);

    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');
    Route::put('attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');
    Route::delete('attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
    Route::patch('attendances/{attendance}/verify', [AttendanceController::class, 'verify'])->name('attendances.verify');
    Route::patch('attendances/{attendance}/reject', [AttendanceController::class, 'reject'])->name('attendances.reject');

    Route::post('members/{member}/account', [MemberAccountController::class, 'store'])
        ->name('members.account.store');
    Route::patch('members/{member}/account/reset-password', [MemberAccountController::class, 'resetPassword'])
        ->name('members.account.reset-password');
    Route::get('members/import', [MemberImportTemplateController::class, 'show'])
        ->name('members.import');
    Route::post('members/import', [MemberImportTemplateController::class, 'store'])
        ->name('members.import.store');
    Route::get('members/import/template', [MemberImportTemplateController::class, 'download'])
        ->name('members.import.template');
    Route::get('members/export', MemberExportController::class)
        ->name('members.export');
    Route::resource('members', MemberController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);
});

require __DIR__.'/auth.php';

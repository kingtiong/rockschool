<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Management\CycleController as ManagementCycleController;
use App\Http\Controllers\Management\BranchController as ManagementBranchController;
use App\Http\Controllers\Management\EnrollmentController as ManagementEnrollmentController;
use App\Http\Controllers\Management\FeePlanController as ManagementFeePlanController;
use App\Http\Controllers\Management\PaymentController as ManagementPaymentController;
use App\Http\Controllers\Management\PayoutController as ManagementPayoutController;
use App\Http\Controllers\Management\RescheduleRequestController as ManagementRescheduleRequestController;
use App\Http\Controllers\Management\TeacherShareController as ManagementTeacherShareController;
use App\Http\Controllers\Management\TimetableController as ManagementTimetableController;
use App\Http\Controllers\Student\CycleController as StudentCycleController;
use App\Http\Controllers\Student\PaymentController as StudentPaymentController;
use App\Http\Controllers\Teacher\EarningController as TeacherEarningController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule/{lesson}/complete', [ScheduleController::class, 'complete'])->name('schedule.complete');
    Route::post('/schedule/{lesson}/absence', [ScheduleController::class, 'absence'])->name('schedule.absence');
    Route::post('/schedule/{lesson}/request-change', [ScheduleController::class, 'requestChange'])->name('schedule.request-change');

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/cycles', [StudentCycleController::class, 'index'])->name('cycles.index');
        Route::get('/cycles/{cycle}/payment', [StudentPaymentController::class, 'create'])->name('cycles.payment.create');
        Route::post('/cycles/{cycle}/payment', [StudentPaymentController::class, 'store'])->name('cycles.payment.store');
    });

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/earnings', [TeacherEarningController::class, 'index'])->name('earnings.index');
    });

    Route::middleware('role:management')->prefix('management')->name('management.')->group(function () {
        Route::get('/timetable', [ManagementTimetableController::class, 'index'])->name('timetable.index');
        Route::get('/timetable/slots/create', [ManagementTimetableController::class, 'createSlot'])->name('timetable.slots.create');
        Route::post('/timetable/slots', [ManagementTimetableController::class, 'storeSlot'])->name('timetable.slots.store');
        Route::post('/timetable/lessons/{lesson}/postpone', [ManagementTimetableController::class, 'postpone'])->name('timetable.lessons.postpone');
        Route::get('/timetable/lessons/{lesson}/reschedule', [ManagementTimetableController::class, 'editReschedule'])->name('timetable.lessons.reschedule.edit');
        Route::put('/timetable/lessons/{lesson}/reschedule', [ManagementTimetableController::class, 'updateReschedule'])->name('timetable.lessons.reschedule.update');

        Route::get('/branches', [ManagementBranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/create', [ManagementBranchController::class, 'create'])->name('branches.create');
        Route::post('/branches', [ManagementBranchController::class, 'store'])->name('branches.store');
        Route::get('/branches/{branch}/edit', [ManagementBranchController::class, 'edit'])->name('branches.edit');
        Route::put('/branches/{branch}', [ManagementBranchController::class, 'update'])->name('branches.update');
        Route::delete('/branches/{branch}', [ManagementBranchController::class, 'destroy'])->name('branches.destroy');

        Route::get('/fee-plans', [ManagementFeePlanController::class, 'index'])->name('fee-plans.index');
        Route::get('/fee-plans/{feePlan}/edit', [ManagementFeePlanController::class, 'edit'])->name('fee-plans.edit');
        Route::put('/fee-plans/{feePlan}', [ManagementFeePlanController::class, 'update'])->name('fee-plans.update');

        Route::get('/enrollments', [ManagementEnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/enrollments/create', [ManagementEnrollmentController::class, 'create'])->name('enrollments.create');
        Route::post('/enrollments', [ManagementEnrollmentController::class, 'store'])->name('enrollments.store');

        Route::get('/enrollments/{enrollment}/cycles/create', [ManagementCycleController::class, 'create'])->name('cycles.create');
        Route::post('/enrollments/{enrollment}/cycles', [ManagementCycleController::class, 'store'])->name('cycles.store');

        Route::get('/payments', [ManagementPaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/{payment}/approve', [ManagementPaymentController::class, 'approve'])->name('payments.approve');
        Route::post('/payments/{payment}/reject', [ManagementPaymentController::class, 'reject'])->name('payments.reject');

        Route::get('/teacher-shares', [ManagementTeacherShareController::class, 'index'])->name('teacher-shares.index');
        Route::post('/teacher-shares/{teacher}', [ManagementTeacherShareController::class, 'upsert'])->name('teacher-shares.upsert');

        Route::get('/payouts', [ManagementPayoutController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{teacher}/pay-all', [ManagementPayoutController::class, 'payAllUnpaid'])->name('payouts.pay-all');
        Route::post('/payouts/{payout}/mark-paid', [ManagementPayoutController::class, 'markPaid'])->name('payouts.mark-paid');

        Route::get('/reschedule-requests', [ManagementRescheduleRequestController::class, 'index'])->name('reschedule-requests.index');
        Route::post('/reschedule-requests/{rescheduleRequest}/approve', [ManagementRescheduleRequestController::class, 'approve'])->name('reschedule-requests.approve');
        Route::post('/reschedule-requests/{rescheduleRequest}/reject', [ManagementRescheduleRequestController::class, 'reject'])->name('reschedule-requests.reject');
    });
});

require __DIR__.'/auth.php';

<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Models\Attendance;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');
Route::post('/admin/login', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt([
        'email' => $request->input('email'),
        'password' => $request->input('password'),
        'role' => 'admin',
    ])) {
        $request->session()->regenerate();
        return redirect('/admin/attendance/list');
    }

    return redirect()
        ->route('admin.login')
        ->withErrors(['email' => 'ログイン情報が登録されていません'])
        ->withInput($request->only('email'));
})->name('admin.login.submit');

Route::get('/admin/attendance/list', function () {
    $dateParam = request()->query('date');
    try {
        $date = $dateParam ? Carbon::createFromFormat('Y-m-d', $dateParam) : Carbon::today();
    } catch (\Throwable $e) {
        $date = Carbon::today();
    }
    $users = \App\Models\User::where('role', 'user')->get();
    $attendances = Attendance::where('work_date', $date)->get()->keyBy('user_id');

    $rows = $users->map(function ($user) use ($attendances) {
        $attendance = $attendances->get($user->id);
        $clockIn = $attendance?->clock_in;
        $clockOut = $attendance?->clock_out;
        $breakMinutes = $attendance?->break_minutes ?? 0;
        if ($breakMinutes === 0) {
            $breakStart = $attendance?->break_start;
            $breakEnd = $attendance?->break_end;
            if ($breakStart && $breakEnd) {
                $breakMinutes = $breakStart->diffInMinutes($breakEnd);
            }
        }

        $totalMinutes = null;
        if ($clockIn && $clockOut) {
            $totalMinutes = max(0, $clockIn->diffInMinutes($clockOut) - $breakMinutes);
        }

        $formatMinutes = function (?int $minutes) {
            if ($minutes === null) {
                return '-';
            }
            $hours = intdiv($minutes, 60);
            $mins = $minutes % 60;
            return sprintf('%d:%02d', $hours, $mins);
        };

        return [
            'user' => $user,
            'clock_in' => $clockIn?->format('H:i') ?? '-',
            'clock_out' => $clockOut?->format('H:i') ?? '-',
            'break' => $formatMinutes($breakMinutes ?: null),
            'total' => $formatMinutes($totalMinutes),
        ];
    });

    return view('admin.attendance', [
        'date' => $date,
        'rows' => $rows,
    ]);
});
Route::get('/admin/attendance/{user}', [AdminAttendanceController::class, 'detail'])
    ->name('admin.attendance.detail');
Route::post('/admin/attendance/{user}', [AdminAttendanceController::class, 'update'])
    ->name('admin.attendance.update');

Route::get('/admin/attendance/staff/{user}', [AdminAttendanceController::class, 'monthly'])
    ->name('admin.staff.attendance');
Route::get('/admin/attendance/staff/{user}/csv', [AdminAttendanceController::class, 'exportCsv'])
    ->name('admin.staff.attendance.csv');

Route::get('/admin/staff/list', [StaffController::class, 'index']);

Route::get('/stamp_correction_request/approve/{requestItem}', [AdminRequestController::class, 'show'])
    ->name('admin.request.detail');
Route::post('/stamp_correction_request/approve/{requestItem}', [AdminRequestController::class, 'approve'])
    ->name('admin.request.approve');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance/list', [AttendanceController::class, 'listMonthly'])->name('attendance.list');
    Route::get('/attendance/detail/{date}', [AttendanceController::class, 'showDetail'])
        ->where('date', '\d{4}-\d{2}-\d{2}')
        ->name('attendance.detail');
    Route::post('/attendance/detail/{date}/request', [AttendanceController::class, 'submitCorrection'])
        ->where('date', '\d{4}-\d{2}-\d{2}')
        ->name('attendance.request.submit');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockin');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockout');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break.start');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break.end');

    Route::get('/attendance', [AttendanceController::class, 'showToday'])->name('attendance.start');
    Route::get('/attendance/working', function () {
        return redirect()->route('attendance.start');
    })->name('attendance.working');
    Route::get('/attendance/break', function () {
        return redirect()->route('attendance.start');
    })->name('attendance.break');
    Route::get('/attendance/done', function () {
        return redirect()->route('attendance.start');
    })->name('attendance.done');
});

Route::get('/stamp_correction_request/list', function (Request $request) {
    $user = $request->user();
    if (!$user) {
        return redirect()->route('login');
    }
    if ($user->role === 'admin') {
        return app(AdminRequestController::class)->index($request);
    }

    if (!$user->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    return app(AttendanceController::class)->listRequests($request);
})->middleware('auth')->name('request.list');

Route::post('/logout', function (Request $request) {
    $role = $request->user()?->role;
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if ($role === 'admin') {
        return redirect()->route('admin.login');
    }

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $request->session()->forget('pending_verification_email');
    return redirect()->route('attendance.start');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();
    if (!$user) {
        $email = $request->session()->get('pending_verification_email');
        if ($email) {
            $user = \App\Models\User::where('email', $email)->first();
        }
    }
    if ($user) {
        $user->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    }

    return redirect()->route('register');
})->middleware(['throttle:6,1'])->name('verification.send');

Route::get('/email/verified', function () {
    return redirect()->route('attendance.start');
})->middleware(['auth', 'verified'])->name('verification.success');

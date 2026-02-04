<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function listMonthly(Request $request)
    {
        $user = Auth::user();
        $month = $request->query('month');

        try {
            $currentMonth = $month
                ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Throwable $e) {
            $currentMonth = Carbon::now()->startOfMonth();
        }

        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(function ($attendance) {
                return $attendance->work_date->toDateString();
            });

        $rows = [];
        $day = $start->copy();
        while ($day->lte($end)) {
            $attendance = $attendances->get($day->toDateString());
            $clockIn = $attendance?->clock_in;
            $clockOut = $attendance?->clock_out;
            $breakMinutes = $attendance?->break_minutes;
            if ($breakMinutes === null) {
                $breakStart = $attendance?->break_start;
                $breakEnd = $attendance?->break_end;
                if ($breakStart && $breakEnd) {
                    $breakMinutes = $breakStart->diffInMinutes($breakEnd);
                }
            }

            $totalMinutes = null;
            if ($clockIn && $clockOut) {
                $totalMinutes = max(0, $clockIn->diffInMinutes($clockOut) - ($breakMinutes ?? 0));
            }

            $rows[] = [
                'date' => $day->copy(),
                'clock_in' => $clockIn?->format('H:i') ?? '-',
                'clock_out' => $clockOut?->format('H:i') ?? '-',
                'break' => $this->formatMinutes($breakMinutes),
                'total' => $this->formatMinutes($totalMinutes),
            ];

            $day->addDay();
        }

        return view('attendance.list', [
            'rows' => $rows,
            'currentMonth' => $currentMonth,
        ]);
    }

    public function showToday()
    {
        $user = Auth::user();
        $date = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $date->toDateString())
            ->first();

        $status = $attendance?->status ?? 'off';
        if ($attendance) {
            if ($attendance->break_started_at) {
                $status = 'break';
            } elseif ($attendance->clock_in && !$attendance->clock_out) {
                $status = $attendance->status === 'off' ? 'working' : $attendance->status;
            } elseif ($attendance->clock_out) {
                $status = 'done';
            }
        }

        $statusLabel = match ($status) {
            'working' => '出勤中',
            'break' => '休憩中',
            'done' => '退勤済',
            default => '勤務外',
        };

        return view('attendance.start', [
            'status' => $status,
            'statusLabel' => $statusLabel,
        ]);
    }

    public function showDetail(string $date)
    {
        $user = Auth::user();

        try {
            $workDate = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable $e) {
            abort(404);
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $workDate->toDateString())
            ->first();

        $breaks = [];
        if ($attendance) {
            $breaks = AttendanceBreak::where('attendance_id', $attendance->id)
                ->orderBy('started_at')
                ->get()
                ->map(function ($break) {
                    return [
                        'start' => $break->started_at?->format('H:i') ?? '',
                        'end' => $break->ended_at?->format('H:i') ?? '',
                    ];
                })
                ->toArray();

            if ($attendance->break_started_at) {
                $breaks[] = [
                    'start' => $attendance->break_started_at?->format('H:i') ?? '',
                    'end' => '',
                ];
            }
        }

        $pendingRequest = AttendanceRequest::where('user_id', $user->id)
            ->where('work_date', $workDate->toDateString())
            ->where('status', 'pending')
            ->latest()
            ->first();

        $displayClockIn = $pendingRequest?->requested_clock_in ?? $attendance?->clock_in;
        $displayClockOut = $pendingRequest?->requested_clock_out ?? $attendance?->clock_out;
        $displayBreakStart = $pendingRequest?->requested_break_start ?? $attendance?->break_start;
        $displayBreakEnd = $pendingRequest?->requested_break_end ?? $attendance?->break_end;

        return view('attendance.detail', [
            'user' => $user,
            'workDate' => $workDate,
            'clockIn' => $displayClockIn?->format('H:i') ?? '',
            'clockOut' => $displayClockOut?->format('H:i') ?? '',
            'breakStart' => $displayBreakStart?->format('H:i') ?? '',
            'breakEnd' => $displayBreakEnd?->format('H:i') ?? '',
            'reason' => $pendingRequest?->reason ?? '',
            'isPending' => $pendingRequest !== null,
            'breaks' => $breaks,
        ]);
    }

    public function submitCorrection(Request $request, string $date)
    {
        $user = Auth::user();

        try {
            $workDate = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable $e) {
            abort(404);
        }

        $existing = AttendanceRequest::where('user_id', $user->id)
            ->where('work_date', $workDate->toDateString())
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return redirect()
                ->route('attendance.detail', ['date' => $workDate->toDateString()])
                ->with('status', '承認待ちのため修正はできません。');
        }

        $validator = Validator::make($request->all(), [
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'reason.required' => '備考を記入してください',
        ]);

        $validator->after(function ($validator) use ($request, $workDate) {
            $clockIn = $this->combineDateTime($workDate, $request->input('clock_in'));
            $clockOut = $this->combineDateTime($workDate, $request->input('clock_out'));
            $breakStart = $this->combineDateTime($workDate, $request->input('break_start'));
            $breakEnd = $this->combineDateTime($workDate, $request->input('break_end'));

            if ($clockIn && $clockOut && $clockIn->gt($clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            if ($breakStart) {
                if ($clockIn && $breakStart->lt($clockIn)) {
                    $validator->errors()->add('break_start', '休憩時間が不適切な値です');
                }
                if ($clockOut && $breakStart->gt($clockOut)) {
                    $validator->errors()->add('break_start', '休憩時間が不適切な値です');
                }
            }

            if ($breakEnd && $clockOut && $breakEnd->gt($clockOut)) {
                $validator->errors()->add('break_end', '休憩時間もしくは退勤時間が不適切な値です');
            }
        });

        $validated = $validator->validate();

        AttendanceRequest::create([
            'user_id' => $user->id,
            'work_date' => $workDate->toDateString(),
            'requested_clock_in' => $this->combineDateTime($workDate, $validated['clock_in'] ?? null),
            'requested_clock_out' => $this->combineDateTime($workDate, $validated['clock_out'] ?? null),
            'requested_break_start' => $this->combineDateTime($workDate, $validated['break_start'] ?? null),
            'requested_break_end' => $this->combineDateTime($workDate, $validated['break_end'] ?? null),
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()
            ->route('attendance.detail', ['date' => $workDate->toDateString()])
            ->with('status', '承認待ちのため修正はできません。');
    }

    public function listRequests(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status', 'pending');
        $status = $status === 'approved' ? 'approved' : 'pending';

        $requests = AttendanceRequest::with('user')
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderByDesc('work_date')
            ->orderByDesc('created_at')
            ->get();

        return view('attendance.request', [
            'requests' => $requests,
            'status' => $status,
        ]);
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $date],
            ['clock_in' => Carbon::now(), 'status' => 'working']
        );

        if ($attendance->status === 'off' && $attendance->clock_in === null) {
            $attendance->clock_in = Carbon::now();
            $attendance->status = 'working';
            $attendance->save();
        }

        return redirect()->route('attendance.start');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $date]
        );

        if ($attendance->status === 'working' && $attendance->clock_out === null) {
            $attendance->clock_out = Carbon::now();
            $attendance->status = 'done';
            $attendance->save();
        }

        return redirect()->route('attendance.start');
    }

    public function breakStart(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $date]
        );

        if ($attendance->status === 'working' && $attendance->break_started_at === null) {
            $attendance->break_started_at = Carbon::now();
            $attendance->status = 'break';
            $attendance->save();
        }

        return redirect()->route('attendance.start');
    }

    public function breakEnd(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $date]
        );

        if ($attendance->status === 'break' && $attendance->break_started_at) {
            $breakMinutes = $attendance->break_started_at->diffInMinutes(Carbon::now());
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'started_at' => $attendance->break_started_at,
                'ended_at' => Carbon::now(),
                'minutes' => $breakMinutes,
            ]);

            $attendance->break_minutes += $breakMinutes;
            $attendance->break_started_at = null;
            $attendance->status = 'working';
            $attendance->save();
        }

        return redirect()->route('attendance.start');
    }

    private function formatMinutes(?int $minutes): string
    {
        if ($minutes === null) {
            return '-';
        }
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }

    private function combineDateTime(Carbon $date, ?string $time): ?Carbon
    {
        if (!$time) {
            return null;
        }
        return Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $time);
    }
}

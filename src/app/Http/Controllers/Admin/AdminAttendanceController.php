<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminAttendanceController extends Controller
{
    public function monthly(Request $request, User $user)
    {
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

        return view('admin.staff-attendance', [
            'user' => $user,
            'rows' => $rows,
            'currentMonth' => $currentMonth,
        ]);
    }

    public function exportCsv(Request $request, User $user)
    {
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
                'date' => $day->copy()->format('Y-m-d'),
                'clock_in' => $clockIn?->format('H:i') ?? '',
                'clock_out' => $clockOut?->format('H:i') ?? '',
                'break' => $this->formatMinutes($breakMinutes),
                'total' => $this->formatMinutes($totalMinutes),
            ];

            $day->addDay();
        }

        $filename = sprintf('%s_%s.csv', $user->name, $currentMonth->format('Y-m'));

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['clock_in'],
                    $row['clock_out'],
                    $row['break'],
                    $row['total'],
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function detail(Request $request, User $user)
    {
        $dateParam = $request->query('date');
        try {
            $workDate = $dateParam ? Carbon::createFromFormat('Y-m-d', $dateParam)->startOfDay() : Carbon::today();
        } catch (\Throwable $e) {
            $workDate = Carbon::today();
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $workDate->toDateString())
            ->first();

        $pendingRequest = AttendanceRequest::where('user_id', $user->id)
            ->where('work_date', $workDate->toDateString())
            ->where('status', 'pending')
            ->latest()
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

        return view('admin.attendance-detail', [
            'user' => $user,
            'workDate' => $workDate,
            'clockIn' => $attendance?->clock_in?->format('H:i') ?? '',
            'clockOut' => $attendance?->clock_out?->format('H:i') ?? '',
            'breaks' => $breaks,
            'remark' => $attendance?->remark ?? '',
            'isPending' => $pendingRequest !== null,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $dateParam = $request->query('date');
        try {
            $workDate = $dateParam ? Carbon::createFromFormat('Y-m-d', $dateParam)->startOfDay() : Carbon::today();
        } catch (\Throwable $e) {
            $workDate = Carbon::today();
        }

        $pendingRequest = AttendanceRequest::where('user_id', $user->id)
            ->where('work_date', $workDate->toDateString())
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return redirect()
                ->route('admin.attendance.detail', ['user' => $user->id, 'date' => $workDate->format('Y-m-d')])
                ->with('status', '承認待ちのため修正はできません。');
        }

        $validator = Validator::make($request->all(), [
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
            'remark' => ['required', 'string', 'max:255'],
        ], [
            'remark.required' => '備考を記入してください',
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

        $clockIn = $this->combineDateTime($workDate, $validated['clock_in'] ?? null);
        $clockOut = $this->combineDateTime($workDate, $validated['clock_out'] ?? null);
        $breakStart = $this->combineDateTime($workDate, $validated['break_start'] ?? null);
        $breakEnd = $this->combineDateTime($workDate, $validated['break_end'] ?? null);

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $workDate->toDateString()]
        );

        $attendance->clock_in = $clockIn;
        $attendance->clock_out = $clockOut;
        $attendance->break_start = $breakStart;
        $attendance->break_end = $breakEnd;
        $attendance->break_started_at = null;
        $attendance->remark = $validated['remark'] ?? null;

        $breakMinutes = 0;
        if ($breakStart && $breakEnd) {
            $breakMinutes = $breakStart->diffInMinutes($breakEnd);
        }
        $attendance->break_minutes = $breakMinutes;

        if ($clockOut) {
            $attendance->status = 'done';
        } elseif ($clockIn) {
            $attendance->status = 'working';
        } else {
            $attendance->status = 'off';
        }

        $attendance->save();

        AttendanceBreak::where('attendance_id', $attendance->id)->delete();
        if ($breakStart && $breakEnd) {
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'started_at' => $breakStart,
                'ended_at' => $breakEnd,
                'minutes' => $breakMinutes,
            ]);
        }

        return redirect()->route('admin.attendance.detail', [
            'user' => $user->id,
            'date' => $workDate->format('Y-m-d'),
        ]);
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

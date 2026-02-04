<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $status = $status === 'approved' ? 'approved' : 'pending';

        $requests = AttendanceRequest::with('user')
            ->where('status', $status)
            ->orderByDesc('work_date')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.request', [
            'requests' => $requests,
            'status' => $status,
        ]);
    }

    public function show(AttendanceRequest $requestItem)
    {
        $user = $requestItem->user;

        return view('admin.request-detail', [
            'requestItem' => $requestItem,
            'user' => $user,
        ]);
    }

    public function approve(AttendanceRequest $requestItem)
    {
        Attendance::updateOrCreate(
            ['user_id' => $requestItem->user_id, 'work_date' => $requestItem->work_date->toDateString()],
            [
                'clock_in' => $requestItem->requested_clock_in,
                'clock_out' => $requestItem->requested_clock_out,
                'break_start' => $requestItem->requested_break_start,
                'break_end' => $requestItem->requested_break_end,
                'remark' => $requestItem->reason,
            ]
        );

        $requestItem->status = 'approved';
        $requestItem->approved_at = Carbon::now();
        $requestItem->save();

        return redirect()->route('request.list', ['status' => 'pending']);
    }
}

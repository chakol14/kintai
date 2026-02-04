@extends('layouts.app')

@section('title', '勤怠一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('nav')
<a class="nav-link" href="{{ url('/admin/attendance/list') }}">勤怠一覧</a>
<a class="nav-link" href="{{ url('/admin/staff/list') }}">スタッフ一覧</a>
<a class="nav-link" href="{{ route('request.list') }}">申請一覧</a>
<form class="nav-form" action="{{ route('logout') }}" method="post">
    @csrf
    <button class="nav-link nav-button" type="submit">ログアウト</button>
</form>
@endsection

@section('content')
@php($prevDate = $date->copy()->subDay())
@php($nextDate = $date->copy()->addDay())
<section class="attendance">
    <h1 class="attendance-title">
        <span class="title-bar">|</span>
        <span class="attendance-date">{{ $date->format('Y年n月j日') }}</span>の勤怠
    </h1>

    <div class="attendance-toolbar">
        <a class="nav-day prev-day" href="{{ url('/admin/attendance/list') }}?date={{ $prevDate->format('Y-m-d') }}">← 前日</a>
        <div class="current-day" role="button" tabindex="0">
            <span class="calendar-icon" aria-hidden="true"></span>
            <span class="date-text">{{ $date->format('Y/m/d') }}</span>
            <input
                class="date-input"
                type="date"
                name="attendance_date"
                value="{{ $date->format('Y-m-d') }}"
                aria-label="日付を選択">
        </div>
        <a class="nav-day next-day" href="{{ url('/admin/attendance/list') }}?date={{ $nextDate->format('Y-m-d') }}">翌日 →</a>
    </div>

    <div class="attendance-table">
        <div class="table-row table-head">
            <div>名前</div>
            <div>出勤</div>
            <div>退勤</div>
            <div>休憩</div>
            <div>合計</div>
            <div>詳細</div>
        </div>
        @forelse($rows ?? [] as $row)
        <div class="table-row">
            <div>{{ $row['user']->name }}</div>
            <div>{{ $row['clock_in'] }}</div>
            <div>{{ $row['clock_out'] }}</div>
            <div>{{ $row['break'] }}</div>
            <div>{{ $row['total'] }}</div>
            <div><a class="detail-link" href="{{ route('admin.attendance.detail', ['user' => $row['user']->id, 'date' => $date->format('Y-m-d')]) }}">詳細</a></div>
        </div>
        @empty
        <div class="table-row">
            <div style="grid-column: 1 / -1; text-align: center; padding: 20px;">ユーザが登録されていません</div>
        </div>
        @endforelse
    </div>
</section>
@endsection

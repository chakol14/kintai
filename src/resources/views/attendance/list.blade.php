@extends('layouts.app')

@section('title', '勤怠一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('nav')
    @include('attendance.partials.nav')
@endsection

@section('content')
@php
    $prevMonth = $currentMonth->copy()->subMonth();
    $nextMonth = $currentMonth->copy()->addMonth();
    $monthLabel = $currentMonth->format('Y年n月');
@endphp
<section class="attendance">
    <h1 class="attendance-title">
        <span class="title-bar">|</span>
        勤怠一覧
    </h1>

    <div class="attendance-toolbar">
        <a class="nav-day" href="{{ route('attendance.list', ['month' => $prevMonth->format('Y-m')]) }}">← 前月</a>
        <div class="current-day">
            <span class="calendar-icon" aria-hidden="true"></span>
            <span class="date-text">{{ $monthLabel }}</span>
        </div>
        <a class="nav-day" href="{{ route('attendance.list', ['month' => $nextMonth->format('Y-m')]) }}">翌月 →</a>
    </div>

    <div class="attendance-table">
        <div class="table-row table-head">
            <div>日付</div>
            <div>出勤</div>
            <div>退勤</div>
            <div>休憩</div>
            <div>合計</div>
            <div>詳細</div>
        </div>
        @foreach($rows as $row)
        <div class="table-row">
            <div>{{ $row['date']->format('Y/m/d') }} ({{ $row['date']->isoFormat('ddd') }})</div>
            <div>{{ $row['clock_in'] }}</div>
            <div>{{ $row['clock_out'] }}</div>
            <div>{{ $row['break'] }}</div>
            <div>{{ $row['total'] }}</div>
            <div><a class="detail-link" href="{{ route('attendance.detail', ['date' => $row['date']->format('Y-m-d')]) }}">詳細</a></div>
        </div>
        @endforeach
    </div>
</section>
@endsection

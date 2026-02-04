@extends('layouts.app')

@section('title', '勤怠')

@section('nav')
    @include('attendance.partials.nav')
@endsection

@section('content')
    <section class="attendance-register">
        <span class="status-badge">{{ $statusLabel }}</span>
        <p class="attendance-date"></p>
        <p class="attendance-time"></p>
        <div class="action-row">
            @if($status === 'off')
                <form action="{{ route('attendance.clockin') }}" method="post">
                    @csrf
                    <button class="action-button primary" type="submit">出勤</button>
                </form>
            @elseif($status === 'working')
                <form action="{{ route('attendance.clockout') }}" method="post">
                    @csrf
                    <button class="action-button primary" type="submit">退勤</button>
                </form>
                <form action="{{ route('attendance.break.start') }}" method="post">
                    @csrf
                    <button class="action-button ghost" type="submit">休憩入</button>
                </form>
            @elseif($status === 'break')
                <form action="{{ route('attendance.break.end') }}" method="post">
                    @csrf
                    <button class="action-button ghost" type="submit">休憩戻</button>
                </form>
            @elseif($status === 'done')
                <p class="attendance-message">お疲れ様でした。</p>
            @endif
        </div>
    </section>
@endsection

@include('attendance.partials.clock-script')

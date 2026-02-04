@extends('layouts.app')

@section('title', '勤怠')

@section('nav')
    @include('attendance.partials.nav')
@endsection

@section('content')
    <section class="attendance-register">
        <span class="status-badge">出勤中</span>
        <p class="attendance-date"></p>
        <p class="attendance-time"></p>
        <div class="action-row">
            <form action="{{ route('attendance.clockout') }}" method="post">
                @csrf
                <button class="action-button primary" type="submit">退勤</button>
            </form>
            <form action="{{ route('attendance.break.start') }}" method="post">
                @csrf
                <button class="action-button ghost" type="submit">休憩入</button>
            </form>
        </div>
    </section>
@endsection

@include('attendance.partials.clock-script')

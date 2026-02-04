@extends('layouts.app')

@section('title', '勤怠')

@section('nav')
    @include('attendance.partials.nav')
@endsection

@section('content')
    <section class="attendance-register">
        <span class="status-badge">退勤済</span>
        <p class="attendance-date"></p>
        <p class="attendance-time"></p>
        <p class="attendance-message">お疲れ様でした。</p>
    </section>
@endsection

@include('attendance.partials.clock-script')

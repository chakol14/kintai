@extends('layouts.app')

@section('title', '勤怠')

@section('nav')
    @include('attendance.partials.nav')
@endsection

@section('content')
    <section class="attendance-register">
        <span class="status-badge">休憩中</span>
        <p class="attendance-date"></p>
        <p class="attendance-time"></p>
        <form action="{{ route('attendance.break.end') }}" method="post">
            @csrf
            <button class="action-button ghost" type="submit">休憩戻</button>
        </form>
    </section>
@endsection

@include('attendance.partials.clock-script')

@extends('layouts.app')

@section('title', '勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('nav')
    @include('attendance.partials.nav')
@endsection

@section('content')
<section class="attendance-detail">
    <h1 class="attendance-title">
        <span class="title-bar">|</span>
        勤怠詳細
    </h1>

    @if($errors->any())
        <div class="alert alert-error">
            <ul class="alert-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="detail-form" action="{{ route('attendance.request.submit', ['date' => $workDate->format('Y-m-d')]) }}" method="post">
        @csrf
        <div class="detail-table">
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value">{{ $user->name }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">日付</div>
                <div class="detail-value">{{ $workDate->format('Y年n月j日') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">出勤・退勤</div>
                <div class="detail-value detail-range">
                    <input class="detail-input" type="time" name="clock_in" value="{{ old('clock_in', $clockIn) }}" {{ $isPending ? 'disabled' : '' }}>
                    <span class="detail-separator">〜</span>
                    <input class="detail-input" type="time" name="clock_out" value="{{ old('clock_out', $clockOut) }}" {{ $isPending ? 'disabled' : '' }}>
                </div>
            </div>
        <div class="detail-row">
            <div class="detail-label">休憩</div>
            <div class="detail-value detail-range">
                <input class="detail-input" type="time" name="break_start" value="{{ old('break_start', $breakStart ?: ($breaks[0]['start'] ?? '')) }}" {{ $isPending ? 'disabled' : '' }}>
                <span class="detail-separator">〜</span>
                <input class="detail-input" type="time" name="break_end" value="{{ old('break_end', $breakEnd ?: ($breaks[0]['end'] ?? '')) }}" {{ $isPending ? 'disabled' : '' }}>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">休憩2</div>
            <div class="detail-value detail-range">
                <span>{{ $breaks[1]['start'] ?? '' }}</span>
                <span class="detail-separator">〜</span>
                <span>{{ $breaks[1]['end'] ?? '' }}</span>
            </div>
        </div>
        @if(count($breaks) > 2)
            @foreach(array_slice($breaks, 2) as $index => $break)
            <div class="detail-row">
                <div class="detail-label">休憩{{ $index + 3 }}</div>
                <div class="detail-value detail-range">
                    <span>{{ $break['start'] ?? '' }}</span>
                    <span class="detail-separator">〜</span>
                    <span>{{ $break['end'] ?? '' }}</span>
                </div>
            </div>
            @endforeach
        @endif
        <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="detail-value">
                <textarea class="detail-textarea" name="reason" rows="2" {{ $isPending ? 'disabled' : '' }}>{{ old('reason', $reason) }}</textarea>
            </div>
        </div>
        </div>

        <div class="detail-actions">
            @if($isPending)
                <p class="detail-warning">＊承認待ちのため修正はできません。</p>
            @else
                <button class="action-button primary" type="submit">修正</button>
            @endif
        </div>
    </form>
</section>
@endsection

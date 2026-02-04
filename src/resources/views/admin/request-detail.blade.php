@extends('layouts.app')

@section('title', '申請詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
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
<section class="attendance-detail">
    <h1 class="attendance-title">
        <span class="title-bar">|</span>
        申請詳細
    </h1>

    <div class="detail-table">
        <div class="detail-row">
            <div class="detail-label">名前</div>
            <div class="detail-value">{{ $user->name }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">日付</div>
            <div class="detail-value">{{ $requestItem->work_date->format('Y年n月j日') }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">出勤・退勤</div>
            <div class="detail-value detail-range">
                <span>{{ $requestItem->requested_clock_in?->format('H:i') ?? '-' }}</span>
                <span class="detail-separator">〜</span>
                <span>{{ $requestItem->requested_clock_out?->format('H:i') ?? '-' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">休憩</div>
            <div class="detail-value detail-range">
                <span>{{ $requestItem->requested_break_start?->format('H:i') ?? '-' }}</span>
                <span class="detail-separator">〜</span>
                <span>{{ $requestItem->requested_break_end?->format('H:i') ?? '-' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">申請理由</div>
            <div class="detail-value">{{ $requestItem->reason }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">申請日時</div>
            <div class="detail-value">{{ $requestItem->created_at->format('Y/m/d') }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">状態</div>
            <div class="detail-value">{{ $requestItem->status === 'approved' ? '承認済み' : '承認待ち' }}</div>
        </div>
    </div>

    <div class="detail-actions">
        @if($requestItem->status === 'pending')
            <form action="{{ route('admin.request.approve', ['requestItem' => $requestItem->id]) }}" method="post">
                @csrf
                <button class="action-button primary" type="submit">承認</button>
            </form>
        @else
            <p class="detail-message">承認済みの申請です。</p>
        @endif
    </div>
</section>
@endsection

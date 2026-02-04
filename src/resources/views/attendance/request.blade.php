@extends('layouts.app')

@section('title', '申請一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endpush

@section('nav')
    @include('attendance.partials.nav')
@endsection

@section('content')
<section class="attendance">
    <h1 class="attendance-title">
        <span class="title-bar">|</span>
        申請一覧
    </h1>

    <div class="request-tabs">
        <a class="request-tab {{ $status === 'pending' ? 'is-active' : '' }}" href="{{ route('request.list', ['status' => 'pending']) }}">承認待ち</a>
        <a class="request-tab {{ $status === 'approved' ? 'is-active' : '' }}" href="{{ route('request.list', ['status' => 'approved']) }}">承認済み</a>
    </div>

    <div class="request-divider"></div>

    <div class="attendance-table">
        <div class="table-row table-head request-head">
            <div>状態</div>
            <div>名前</div>
            <div>対象日時</div>
            <div>申請理由</div>
            <div>申請日時</div>
            <div>詳細</div>
        </div>
        @forelse($requests as $requestItem)
        <div class="table-row request-row">
            <div>{{ $requestItem->status === 'approved' ? '承認済み' : '承認待ち' }}</div>
            <div>{{ $requestItem->user->name }}</div>
            <div>{{ $requestItem->work_date->format('Y/m/d') }}</div>
            <div>{{ $requestItem->reason }}</div>
            <div>{{ $requestItem->created_at->format('Y/m/d') }}</div>
            <div><a class="detail-link" href="{{ route('attendance.detail', ['date' => $requestItem->work_date->format('Y-m-d')]) }}">詳細</a></div>
        </div>
        @empty
        <div class="table-row">
            <div style="grid-column: 1 / -1; text-align: center; padding: 20px;">申請がありません</div>
        </div>
        @endforelse
    </div>
</section>
@endsection

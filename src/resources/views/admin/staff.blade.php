@extends('layouts.app')

@section('title', 'スタッフ一覧')

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
<section class="attendance">
    <h1 class="attendance-title">
        <span class="title-bar">|</span>
        スタッフ一覧
    </h1>

    <div class="attendance-table">
        <div class="table-row table-head staff-head">
            <div>名前</div>
            <div>メールアドレス</div>
            <div>月次勤怠</div>
        </div>
        @forelse($users ?? [] as $user)
        <div class="table-row staff-row">
            <div>{{ $user->name }}</div>
            <div>{{ $user->email }}</div>
            <div><a class="detail-link" href="{{ route('admin.staff.attendance', ['user' => $user->id]) }}">詳細</a></div>
        </div>
        @empty
        <div class="table-row staff-row">
            <div style="grid-column: 1 / -1; text-align: center; padding: 20px;">ユーザが登録されていません</div>
        </div>
        @endforelse
    </div>
</section>
@endsection

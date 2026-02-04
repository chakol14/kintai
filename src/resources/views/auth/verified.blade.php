@extends('layouts.app')

@section('title', 'メール認証完了')

@section('content')
    <section class="login-wrap">
        <div class="login-card verify-card">
            <p class="verify-message">メール認証が完了しました。</p>
            <a class="login-button verify-button" href="{{ url('/admin/attendance/list') }}">勤怠一覧へ</a>
        </div>
    </section>
@endsection

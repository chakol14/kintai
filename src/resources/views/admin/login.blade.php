@extends('layouts.app')

@section('title', '管理者ログイン')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<section class="login-wrap">
    <div class="login-card">
        <h1 class="login-title">管理者ログイン</h1>
        @if($errors->any())
            <div class="alert alert-error">
                <ul class="alert-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form class="login-form" action="{{ route('admin.login.submit') }}" method="post">
            @csrf
            <div class="form-row">
                <label class="form-label" for="email">メールアドレス</label>
                <input class="form-input" id="email" type="email" name="email" autocomplete="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-row">
                <label class="form-label" for="password">パスワード</label>
                <input class="form-input" id="password" type="password" name="password" autocomplete="current-password" required>
            </div>
            <button class="login-button" type="submit">管理者ログインする</button>
        </form>
    </div>
</section>
@endsection

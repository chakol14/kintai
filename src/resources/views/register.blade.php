@extends('layouts.app')

@section('title', '会員登録')

@section('content')
<section class="login-wrap">
    <div class="login-card">
        <h1 class="login-title">会員登録</h1>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <ul class="alert-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form class="login-form" action="{{ route('register') }}" method="post">
            @csrf
            <div class="form-row">
                <label class="form-label" for="name">名前</label>
                <input class="form-input" id="name" type="text" name="name" autocomplete="name" required>
            </div>
            <div class="form-row">
                <label class="form-label" for="email">メールアドレス</label>
                <input class="form-input" id="email" type="email" name="email" autocomplete="email" required>
            </div>
            <div class="form-row">
                <label class="form-label" for="password">パスワード</label>
                <input class="form-input" id="password" type="password" name="password" autocomplete="new-password" required>
            </div>
            <div class="form-row">
                <label class="form-label" for="password_confirmation">パスワード確認</label>
                <input class="form-input" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <button class="login-button" type="submit">登録する</button>
        </form>
        <div class="auth-link-wrap">
            <a class="auth-link" href="{{ route('login') }}">ログインはこちら</a>
        </div>
    </div>
</section>
@endsection

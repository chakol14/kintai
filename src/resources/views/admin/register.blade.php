@extends('layouts.app')

@section('title', 'ユーザー登録')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@section('content')
<section class="register-wrap">
    <div class="register-card">
        <h1 class="register-title">ユーザー登録</h1>
        <form class="register-form" action="#" method="post">
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
            <button class="register-button" type="submit">登録する</button>
        </form>
    </div>
</section>
@endsection
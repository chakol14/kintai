@extends('layouts.app')

@section('title', 'メール認証')

@section('content')
    <section class="login-wrap">
        <div class="login-card verify-card">
            <p class="verify-message">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください
            </p>

            @if (session('status') === 'verification-link-sent')
                <p class="verify-status">認証メールを再送しました。</p>
            @endif

            <form class="verify-actions" method="post" action="{{ route('verification.send') }}">
                @csrf
                <button class="verify-primary" type="submit">認証はこちらから</button>
            </form>

            <form method="post" action="{{ route('verification.send') }}">
                @csrf
                <button class="verify-link-button" type="submit">認証メールを再送する</button>
            </form>
        </div>
    </section>
@endsection

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '勤怠管理アプリ')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>

<body>
    <header class="app-header">
        <div class="header-inner">
            <a class="logo" href="/admin/login">
                <img class="logo-image" src="{{ asset('storage/logo/logo.png') }}" alt="COACHTECH">
            </a>
            @hasSection('nav')
            <nav class="header-nav">
                @yield('nav')
            </nav>
            @endif
        </div>
        @hasSection('page_caption')
        <div class="page-caption">
            @yield('page_caption')
        </div>
        @endif
    </header>

    <main class="app-main">
        @yield('content')
    </main>
    @hasSection('scripts')
    @yield('scripts')
    @endif
</body>

</html>
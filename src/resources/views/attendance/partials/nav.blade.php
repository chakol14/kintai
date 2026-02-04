<a class="nav-link" href="{{ route('attendance.start') }}">勤怠</a>
<a class="nav-link" href="{{ route('attendance.list') }}">勤怠一覧</a>
<a class="nav-link" href="{{ route('request.list') }}">申請</a>
<form class="nav-form" action="{{ route('logout') }}" method="post">
    @csrf
    <button class="nav-link nav-button" type="submit">ログアウト</button>
</form>

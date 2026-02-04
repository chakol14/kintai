@section('scripts')
    <script>
        const formatDate = (date) => {
            const days = ['日', '月', '火', '水', '木', '金', '土'];
            const year = date.getFullYear();
            const month = date.getMonth() + 1;
            const day = date.getDate();
            const weekday = days[date.getDay()];
            return `${year}年${month}月${day}日(${weekday})`;
        };

        const formatTime = (date) => {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        };

        const updateClock = () => {
            const now = new Date();
            const dateEl = document.querySelector('.attendance-date');
            const timeEl = document.querySelector('.attendance-time');
            if (dateEl) dateEl.textContent = formatDate(now);
            if (timeEl) timeEl.textContent = formatTime(now);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                updateClock();
                setInterval(updateClock, 1000);
            });
        } else {
            updateClock();
            setInterval(updateClock, 1000);
        }
    </script>
@endsection

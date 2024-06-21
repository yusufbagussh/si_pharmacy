<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $tittle }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon_pharmacy_white.png') }}">
    @vite('resources/css/style.css')
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .footer {
            position: relative;
            bottom: 0;
            width: 100%;
        }

        .nav-tabs .nav-link.active {
            background-color: #1a6d2c;
            /* Warna hijau lebih gelap untuk tab aktif */
            border-color: #ffffff;
            /* Warna putih untuk border */
            color: #ffffff;
            /* Pastikan teks tetap putih */
        }
    </style>

</head>

<body>
    {{-- @include('partials.navbar') --}}
    <x-layouts.navbar :$active />
    @yield('container')
    <x-layouts.footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function padZero(num) {
            return num.toString().padStart(2, '0');
        }

        function formatDate(date) {
            const day = padZero(date.getDate());
            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const month = monthNames[date.getMonth()];
            const year = date.getFullYear();
            const hours = padZero(date.getHours());
            const minutes = padZero(date.getMinutes());
            const seconds = padZero(date.getSeconds());

            return `${day} ${month} ${year}, ${hours}:${minutes}:${seconds}`;
        }

        function updateDateTime() {
            const dateTimeElement = document.getElementById('current-datetime');
            const now = new Date();
            dateTimeElement.innerHTML = formatDate(now);
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
</body>

</html>

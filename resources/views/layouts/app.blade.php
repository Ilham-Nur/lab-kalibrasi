<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'Lab Kalibration'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('template-admin-2026/style.css') }}">

    @stack('styles')
</head>
<body>
    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>
    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    @include('partials.sidebar')

    <div id="main-wrapper" class="main-wrapper">
        @include('partials.navbar')

        <main class="content">
            @yield('content')
        </main>
    </div>

    @include('partials.modals')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('template-admin-2026/script.js') }}"></script>
    @stack('scripts')
</body>
</html>

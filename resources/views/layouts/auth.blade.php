<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title', 'Login') - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="description" content="@yield('description', 'CRM & Accounts Management System')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    <link href="{{ asset('assets/styles/main.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/styles/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/styles/auth.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @if(session('success') || session('error') || session('warning') || session('info'))
    <script>
        window.__koFlash = {
            @if(session('success')) success: @json(session('success')), @endif
            @if(session('error')) error: @json(session('error')), @endif
            @if(session('warning')) warning: @json(session('warning')), @endif
            @if(session('info')) info: @json(session('info')), @endif
        };
    </script>
    @endif
</head>
<body>
    <div class="app-container app-theme-white body-tabs-shadow">
        @yield('content')
    </div>
    <script src="{{ asset('assets/scripts/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/scripts/vendors.js') }}"></script>
    <script src="{{ asset('assets/scripts/carousel_slider.js') }}"></script>
    <script src="{{ asset('assets/scripts/custom.js') }}"></script>
    @stack('scripts')
</body>
</html>

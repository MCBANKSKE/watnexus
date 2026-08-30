<!DOCTYPE html>
<html lang="en" class="{{ $theme ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F6F9FC">

    {{-- FOUC guard — has to run before entry.css loads. --}}
    <script>
        (function(){
            var saved = localStorage.getItem('nexus-theme');
            var theme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if(theme === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>

    <title>@yield('title', config('app.name', 'Nexus Invoicing'))</title>
    <meta name="description" content="Company Setup — Nexus Invoicing">
    <meta name="author" content="Nexus Invoicing">

    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Self-contained entry assets — these pages do NOT depend on main.css / main.js / Three.js. --}}
    <link href="{{ asset('assets/css/entry.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/company-setup.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body class="auth-page company-setup-page">
    <canvas id="bg-canvas"></canvas>

    @include('layouts.partials.entry-header')

    <div class="page">
        <main>
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('assets/js/entry.js') }}" defer></script>
    <script src="{{ asset('assets/js/company-setup.js') }}" defer></script>

    @stack('scripts')
</body>
</html>
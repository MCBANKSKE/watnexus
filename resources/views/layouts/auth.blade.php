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

    <title>@yield('title', config('app.name', 'Watnexus'))</title>
    <meta name="description" content="Watnexus — Streamline your business operations and get paid faster.">
    <meta name="author" content="Watnexus">

    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Self-contained entry assets — these pages do NOT depend on main.css / main.js / Three.js. --}}
    <link href="{{ asset('assets/css/entry.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/auth.css') }}" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>

<body class="auth-page">
    <canvas id="bg-canvas"></canvas>

    @include('layouts.partials.entry-header')

    <div class="page">
        <main class="entry-main">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>

    <script src="{{ asset('assets/js/entry.js') }}" defer></script>
    <script src="{{ asset('assets/js/auth.js') }}" defer></script>

    @stack('modals')
    @livewireScripts
    @stack('scripts')
</body>
</html>
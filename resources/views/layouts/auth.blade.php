<!DOCTYPE html>
<html lang="en" class="{{ $theme ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F6F9FC">

    {{-- Same FOUC guard as the marketing layout — has to run before nexus.css loads. --}}
    <script>
        (function(){
            var saved = localStorage.getItem('nexus-theme');
            var theme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if(theme === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>

    <title>@yield('title', config('app.name', 'Nexus Invoicing'))</title>
    <meta name="description" content="Nexus Invoicing — Quote. Invoice. Get paid on M-Pesa.">
    <meta name="author" content="Nexus Invoicing">

    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/auth.css') }}" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>

<body class="auth-page">
    <canvas id="bg-canvas"></canvas>

    <div class="page">
        <a href="{{ route('home') }}" class="auth-brand"><span class="brand-mark"></span>Nexus Invoicing</a>
        <button id="theme-toggle" class="theme-toggle auth-theme-toggle" aria-label="Toggle theme" title="Toggle dark / light mode">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        </button>

        <main class="flex-grow">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="{{ asset('assets/js/main.js') }}" defer></script>
    <script src="{{ asset('assets/js/auth.js') }}" defer></script>

    @stack('modals')
    @livewireScripts
    @stack('scripts')
</body>
</html>
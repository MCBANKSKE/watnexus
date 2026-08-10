<!DOCTYPE html>
<html lang="en" class="{{ $theme ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F6F9FC">
    <meta name="description" content="Nexus Invoicing — Professional billing and payment collection for businesses worldwide">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Nexus">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon.png') }}">

    <link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/img/favicon.png') }}">

    {{-- Sets the dark/light class before first paint, so there's no flash of the
         wrong theme for users whose saved or system preference is dark. Has to run
         here, inline, before styles.css loads — everything else stays in nexus.js. --}}
    <script>
        (function() {
            var saved = localStorage.getItem('nexus-theme');
            var theme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <title>@yield('title', config('app.name', 'Nexus Invoicing'))</title>
    <meta name="description" content="@yield('description', 'Nexus Invoicing — Professional billing and payment collection for businesses worldwide. Get paid faster with multiple payment methods including M-Pesa.')">
    <meta name="keywords" content="@yield('keywords', 'Nexus Invoicing, business invoicing, payment collection, billing software, quote to cash, M-Pesa payments, multi-currency')">
    <meta name="author" content="@yield('author', 'Nexus Invoicing')">

    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">

    {{-- Fonts actually used by the design: Fraunces (display), Inter (body), IBM Plex Mono (data/labels) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Site styles --}}
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>

<body class="index-page">
    <canvas id="bg-canvas"></canvas>

    <div class="page">
        <nav id="nav">
            <div class="brand"><img src="{{ asset('assets/img/favicon.png') }}" alt="Nexus Invoicing" class="brand-mark">Nexus Invoicing</div>
            <div class="nav-links">
                <a href="#why-nexus">Why Nexus</a>
                <a href="#features">Features</a>
                <a href="#testimonials">Success Stories</a>
                <a href="#faq">FAQ</a>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <button id="theme-toggle" class="theme-toggle" aria-label="Toggle theme" title="Toggle dark / light mode">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                </button>
                <a href="/login" class="nav-cta">Start Free Trial</a>
            </div>
        </nav>

        <main class="flex-grow">
            @yield('content')
            {{ $slot ?? '' }}
        </main>

        <footer>
            <div class="wrap">
                <div class="footer-content">
                    <div class="footer-section">
                        <div class="brand"><img src="{{ asset('assets/img/favicon.png') }}" alt="Nexus Invoicing" class="brand-mark">Nexus Invoicing</div>
                        <p class="footer-description">Professional billing and payment collection for businesses worldwide. Streamline your invoicing workflow and get paid faster.</p>
                        <div class="footer-social">
                            <a href="#" aria-label="Twitter"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg></a>
                            <a href="#" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></a>
                            <a href="#" aria-label="GitHub"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/></svg></a>
                        </div>
                    </div>
                    
                    <div class="footer-section">
                        <h4>Product</h4>
                        <ul class="footer-links">
                            <li><a href="#why-nexus">Why Nexus</a></li>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#testimonials">Success Stories</a></li>
                            <li><a href="#faq">FAQ</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-section">
                        <h4>Company</h4>
                        <ul class="footer-links">
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-section">
                        <h4>Support</h4>
                        <ul class="footer-links">
                            <li><a href="#">Help Center</a></li>
                            <li><a href="#">Documentation</a></li>
                            <li><a href="#">API Reference</a></li>
                            <li><a href="#">Status</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-section">
                        <h4>Legal</h4>
                        <ul class="footer-links">
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Service</a></li>
                            <li><a href="#">Cookie Policy</a></li>
                            <li><a href="#">GDPR</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <div class="footer-copyright">
                        <span>&copy; 2024 Nexus Invoicing. All rights reserved.</span>
                    </div>
                    <div class="footer-built">
                        <span>Built for Global Businesses</span>
                    </div>
                    <div class="footer-install">
                        <button id="manual-install-btn" class="btn-ghost" style="display: none; font-size: 14px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px; vertical-align: middle;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Install App
                        </button>
                    </div>
                </div>
            </div>
        </footer>

        <a href="#" id="scroll-top" class="scroll-top" aria-label="Back to top">↑</a>
    </div>

    <!-- PWA Install Modal -->
    <div id="pwa-modal-overlay" class="pwa-modal-overlay">
        <div class="pwa-modal">
            <button id="pwa-modal-close" class="pwa-modal-close" aria-label="Close">×</button>
            <img src="{{ asset('assets/img/favicon.png') }}" alt="Nexus Invoicing" class="pwa-modal-icon">
            <h3>Install Nexus Invoicing</h3>
            <p>Install our app for quick access to your invoices, quotes, and payments. Works offline and loads instantly.</p>
            
            <div id="pwa-modal-browser-content">
                <!-- Chrome/Edge content injected by JS -->
            </div>

            <div id="pwa-modal-safari-content" style="display: none;">
                <p style="margin-bottom: 12px;"><strong>Install on iOS:</strong></p>
                <ol class="pwa-modal-safari-steps">
                    <li><strong>1.</strong> Tap the Share button <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin: 0 2px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg> at the bottom of your browser.</li>
                    <li><strong>2.</strong> Scroll down and tap <strong>"Add to Home Screen"</strong>.</li>
                    <li><strong>3.</strong> Tap <strong>"Add"</strong> to install.</li>
                </ol>
            </div>

            <div class="pwa-modal-actions">
                <button id="pwa-modal-install" class="btn-primary" style="display: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px; vertical-align: middle;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Install Now
                </button>
                <button id="pwa-modal-not-now" class="btn-ghost">Not Now</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="{{ asset('assets/js/main.js') }}" defer></script>

    @stack('modals')
    @livewireScripts
    @stack('scripts')
</body>
</html>
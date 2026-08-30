<header class="entry-header" id="entryHeader">
    <div class="entry-header-inner">
        <a class="entry-brand" href="{{ route('home') }}" aria-label="Watnexus — Home">
            <span class="entry-brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </span>
            <span class="entry-brand-name">{{ config('app.name') }}</span>
        </a>

        <nav class="entry-nav" aria-label="Nexus">
            <a class="entry-nav-link" href="{{ route('home') }}">Home</a>
            <a class="entry-nav-link" href="{{ route('privacy-policy') }}">Privacy</a>
            <a class="entry-nav-link" href="{{ route('terms-of-service') }}">Terms</a>
            <button id="theme-toggle" class="entry-theme-toggle" type="button"
                    aria-label="Toggle dark / light mode" title="Toggle dark / light mode">
                <svg class="entry-theme-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                <svg class="entry-theme-moon" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
            </button>
        </nav>
    </div>
</header>
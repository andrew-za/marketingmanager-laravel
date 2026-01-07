<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}MarketPulse - AI-Powered Marketing Automation Platform</title>

    <meta name="description" content="MarketPulse is a comprehensive AI-powered marketing automation platform that streamlines digital campaigns, enhances collaboration, and delivers actionable insights.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <link href="{{ asset('css/public.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="antialiased">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-brand">
                    <a href="{{ route('home') }}" class="logo">
                        <svg class="logo-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="logo-text">MarketPulse</span>
                    </a>
                </div>
                
                <div class="navbar-menu" id="navbarMenu">
                    <a href="{{ route('features') }}" class="nav-link">Features</a>
                    <a href="{{ route('pricing') }}" class="nav-link">Pricing</a>
                    <a href="{{ route('about') }}" class="nav-link">About</a>
                    <a href="{{ route('contact') }}" class="nav-link">Contact</a>
                </div>

                <div class="navbar-actions">
                    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                </div>

                <button class="navbar-toggle" id="navbarToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-brand">
                        <svg class="logo-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="logo-text">MarketPulse</span>
                    </div>
                    <p class="footer-description">
                        AI-powered marketing automation platform designed to streamline campaigns, enhance collaboration, and deliver actionable insights.
                    </p>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title">Product</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('features') }}">Features</a></li>
                        <li><a href="{{ route('pricing') }}">Pricing</a></li>
                        <li><a href="#">Integrations</a></li>
                        <li><a href="#">API</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title">Company</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('about') }}">About</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title">Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">GDPR</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} MarketPulse. All rights reserved.</p>
                <div class="social-links">
                    <a href="#" aria-label="Twitter">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                        </svg>
                    </a>
                    <a href="#" aria-label="LinkedIn">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path>
                        </svg>
                    </a>
                    <a href="#" aria-label="Facebook">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarMenu = document.getElementById('navbarMenu');
        
        if (navbarToggle) {
            navbarToggle.addEventListener('click', () => {
                navbarMenu.classList.toggle('active');
                navbarToggle.classList.toggle('active');
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>



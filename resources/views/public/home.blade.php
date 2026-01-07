@extends('layouts.public')

@section('content')
<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Transform Your Marketing with
                    <span class="text-gradient">AI-Powered Automation</span>
                </h1>
                <p class="hero-description">
                    MarketPulse streamlines your digital campaigns, enhances team collaboration, 
                    and delivers actionable insights. One platform for all your marketing needs.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                        Start Free Trial
                    </a>
                    <a href="#demo" class="btn btn-secondary btn-lg">
                        Watch Demo
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-number">10k+</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">500k+</div>
                        <div class="stat-label">Campaigns Launched</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-illustration">
                    <svg viewBox="0 0 600 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Dashboard -->
                        <rect x="50" y="50" width="500" height="350" rx="12" fill="#fff" stroke="#e5e7eb" stroke-width="2"/>
                        
                        <!-- Header -->
                        <rect x="50" y="50" width="500" height="60" rx="12" fill="#4f46e5" fill-opacity="0.1"/>
                        <circle cx="80" cy="80" r="15" fill="#4f46e5"/>
                        <rect x="110" y="70" width="150" height="20" rx="4" fill="#4f46e5" fill-opacity="0.3"/>
                        
                        <!-- Charts -->
                        <rect x="80" y="140" width="200" height="120" rx="8" fill="#f3f4f6"/>
                        <path d="M 100 240 L 120 210 L 140 220 L 160 190 L 180 200 L 200 170 L 220 180 L 240 160" stroke="#4f46e5" stroke-width="3" fill="none"/>
                        
                        <rect x="320" y="140" width="200" height="120" rx="8" fill="#f3f4f6"/>
                        <circle cx="420" cy="200" r="40" fill="#4f46e5" fill-opacity="0.2" stroke="#4f46e5" stroke-width="20" stroke-dasharray="80 160"/>
                        
                        <!-- Stats Cards -->
                        <rect x="80" y="290" width="140" height="80" rx="8" fill="#10b981" fill-opacity="0.1" stroke="#10b981" stroke-width="2"/>
                        <rect x="250" y="290" width="140" height="80" rx="8" fill="#f59e0b" fill-opacity="0.1" stroke="#f59e0b" stroke-width="2"/>
                        <rect x="420" y="290" width="100" height="80" rx="8" fill="#ef4444" fill-opacity="0.1" stroke="#ef4444" stroke-width="2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features" id="features">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Everything You Need to Succeed</h2>
            <p class="section-description">
                Powerful features designed to streamline your marketing workflow and amplify your results
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">AI Content Generation</h3>
                <p class="feature-description">
                    Generate engaging social media posts, email campaigns, and press releases with advanced AI technology
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Smart Scheduling</h3>
                <p class="feature-description">
                    Schedule campaigns across multiple platforms with AI-powered optimal timing recommendations
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Advanced Analytics</h3>
                <p class="feature-description">
                    Track performance, measure ROI, and gain insights with comprehensive analytics and reporting
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Team Collaboration</h3>
                <p class="feature-description">
                    Collaborate seamlessly with your team, manage approvals, and streamline workflows
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Multi-Platform Publishing</h3>
                <p class="feature-description">
                    Publish to Facebook, Instagram, LinkedIn, Twitter, TikTok, and more from one dashboard
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Campaign Management</h3>
                <p class="feature-description">
                    Create, manage, and optimize multi-channel campaigns with ease and efficiency
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Email Marketing</h3>
                <p class="feature-description">
                    Design beautiful emails, segment audiences, and track engagement with built-in email tools
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Brand Protection</h3>
                <p class="feature-description">
                    Maintain brand consistency with automated guidelines, approval workflows, and asset management
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Social Proof -->
<section class="social-proof">
    <div class="container">
        <p class="social-proof-title">Trusted by leading brands worldwide</p>
        <div class="logos-grid">
            <div class="logo-placeholder">Brand Logo</div>
            <div class="logo-placeholder">Brand Logo</div>
            <div class="logo-placeholder">Brand Logo</div>
            <div class="logo-placeholder">Brand Logo</div>
            <div class="logo-placeholder">Brand Logo</div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="section-description">
                Join thousands of satisfied customers who have transformed their marketing
            </p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <span>★★★★★</span>
                </div>
                <p class="testimonial-text">
                    "MarketPulse has completely transformed our social media strategy. The AI content generation is incredible, and we've seen a 300% increase in engagement."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">SM</div>
                    <div>
                        <div class="author-name">Sarah Mitchell</div>
                        <div class="author-title">Marketing Director, TechCorp</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <span>★★★★★</span>
                </div>
                <p class="testimonial-text">
                    "The multi-platform scheduling and analytics have saved us countless hours. ROI tracking is simple and the insights are invaluable."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">JD</div>
                    <div>
                        <div class="author-name">James Davidson</div>
                        <div class="author-title">CEO, Growth Agency</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <span>★★★★★</span>
                </div>
                <p class="testimonial-text">
                    "Best marketing platform we've used. The collaboration features make it easy for our team to work together, and the automation is a game-changer."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">EK</div>
                    <div>
                        <div class="author-name">Emily Keller</div>
                        <div class="author-title">CMO, Retail Brand</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Transform Your Marketing?</h2>
            <p class="cta-description">
                Join thousands of marketers who are already using MarketPulse to automate their campaigns and drive results.
            </p>
            <div class="cta-actions">
                <a href="{{ route('register') }}" class="btn btn-white btn-lg">
                    Start Your Free Trial
                </a>
                <a href="{{ route('contact') }}" class="btn btn-outline-white btn-lg">
                    Schedule a Demo
                </a>
            </div>
            <p class="cta-note">No credit card required • 14-day free trial • Cancel anytime</p>
        </div>
    </div>
</section>
@endsection


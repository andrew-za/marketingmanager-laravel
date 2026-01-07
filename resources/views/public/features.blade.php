@extends('layouts.public')

@php
    $title = 'Features';
@endphp

@section('content')
<!-- Features Hero -->
<section class="features-hero">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Powerful Features for Modern Marketing</h1>
            <p class="section-description">
                Everything you need to create, manage, and optimize your marketing campaigns in one powerful platform.
            </p>
        </div>
    </div>
</section>

<!-- AI Features -->
<section class="feature-section">
    <div class="container">
        <div class="feature-content">
            <div class="feature-text">
                <div class="feature-label">AI-Powered</div>
                <h2 class="feature-title">Intelligent Content Creation</h2>
                <p class="feature-description">
                    Leverage cutting-edge AI technology to generate engaging content for all your marketing channels. 
                    From social media posts to email campaigns and press releases, create compelling content in seconds.
                </p>
                <ul class="feature-points">
                    <li>Generate platform-specific social media content</li>
                    <li>Create email templates with high conversion rates</li>
                    <li>Write professional press releases</li>
                    <li>Generate blog posts and articles</li>
                    <li>Maintain brand voice consistency across all content</li>
                </ul>
            </div>
            <div class="feature-visual">
                <div class="visual-placeholder">
                    <svg viewBox="0 0 400 300" fill="none">
                        <rect width="400" height="300" fill="#f9fafb"/>
                        <rect x="50" y="50" width="300" height="200" rx="8" fill="white" stroke="#e5e7eb" stroke-width="2"/>
                        <rect x="70" y="70" width="100" height="20" rx="4" fill="#4f46e5" opacity="0.3"/>
                        <rect x="70" y="100" width="260" height="10" rx="4" fill="#e5e7eb"/>
                        <rect x="70" y="120" width="240" height="10" rx="4" fill="#e5e7eb"/>
                        <rect x="70" y="140" width="220" height="10" rx="4" fill="#e5e7eb"/>
                        <rect x="70" y="170" width="120" height="30" rx="8" fill="#4f46e5"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Campaign Management -->
<section class="feature-section alternate">
    <div class="container">
        <div class="feature-content reverse">
            <div class="feature-text">
                <div class="feature-label">Management</div>
                <h2 class="feature-title">Multi-Channel Campaign Management</h2>
                <p class="feature-description">
                    Plan, execute, and track campaigns across all your marketing channels from a single dashboard. 
                    Coordinate social media, email, paid ads, and more with ease.
                </p>
                <ul class="feature-points">
                    <li>Unified campaign dashboard for all channels</li>
                    <li>Budget allocation and tracking</li>
                    <li>Campaign timeline visualization</li>
                    <li>Goal setting and KPI monitoring</li>
                    <li>Real-time performance metrics</li>
                </ul>
            </div>
            <div class="feature-visual">
                <div class="visual-placeholder">
                    <svg viewBox="0 0 400 300" fill="none">
                        <rect width="400" height="300" fill="#f9fafb"/>
                        <rect x="50" y="50" width="120" height="80" rx="8" fill="#10b981" opacity="0.2" stroke="#10b981" stroke-width="2"/>
                        <rect x="190" y="50" width="120" height="80" rx="8" fill="#f59e0b" opacity="0.2" stroke="#f59e0b" stroke-width="2"/>
                        <rect x="50" y="150" width="120" height="80" rx="8" fill="#ef4444" opacity="0.2" stroke="#ef4444" stroke-width="2"/>
                        <rect x="190" y="150" width="120" height="80" rx="8" fill="#4f46e5" opacity="0.2" stroke="#4f46e5" stroke-width="2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Analytics -->
<section class="feature-section">
    <div class="container">
        <div class="feature-content">
            <div class="feature-text">
                <div class="feature-label">Insights</div>
                <h2 class="feature-title">Advanced Analytics & Reporting</h2>
                <p class="feature-description">
                    Make data-driven decisions with comprehensive analytics and customizable reports. 
                    Track performance, measure ROI, and discover optimization opportunities.
                </p>
                <ul class="feature-points">
                    <li>Real-time performance dashboards</li>
                    <li>Custom report builder</li>
                    <li>ROI calculation and tracking</li>
                    <li>Sentiment analysis</li>
                    <li>Competitor benchmarking</li>
                    <li>Predictive analytics</li>
                </ul>
            </div>
            <div class="feature-visual">
                <div class="visual-placeholder">
                    <svg viewBox="0 0 400 300" fill="none">
                        <rect width="400" height="300" fill="#f9fafb"/>
                        <path d="M 50 250 L 100 200 L 150 220 L 200 150 L 250 170 L 300 100 L 350 120" stroke="#4f46e5" stroke-width="4" fill="none"/>
                        <circle cx="100" cy="200" r="6" fill="#4f46e5"/>
                        <circle cx="200" cy="150" r="6" fill="#4f46e5"/>
                        <circle cx="300" cy="100" r="6" fill="#4f46e5"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Collaboration -->
<section class="feature-section alternate">
    <div class="container">
        <div class="feature-content reverse">
            <div class="feature-text">
                <div class="feature-label">Teamwork</div>
                <h2 class="feature-title">Seamless Team Collaboration</h2>
                <p class="feature-description">
                    Work together efficiently with built-in collaboration tools. Manage approvals, assign tasks, 
                    and communicate with your team all in one place.
                </p>
                <ul class="feature-points">
                    <li>Content approval workflows</li>
                    <li>Task management and assignment</li>
                    <li>Real-time team chat</li>
                    <li>Role-based permissions</li>
                    <li>Activity tracking and notifications</li>
                </ul>
            </div>
            <div class="feature-visual">
                <div class="visual-placeholder">
                    <svg viewBox="0 0 400 300" fill="none">
                        <rect width="400" height="300" fill="#f9fafb"/>
                        <circle cx="200" cy="100" r="40" fill="#4f46e5" opacity="0.3"/>
                        <circle cx="150" cy="180" r="35" fill="#10b981" opacity="0.3"/>
                        <circle cx="250" cy="180" r="35" fill="#f59e0b" opacity="0.3"/>
                        <circle cx="120" cy="240" r="30" fill="#ef4444" opacity="0.3"/>
                        <circle cx="280" cy="240" r="30" fill="#8b5cf6" opacity="0.3"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- All Features Grid -->
<section class="all-features">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">And Much More...</h2>
        </div>

        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon-small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3>Content Calendar</h3>
                <p>Visual scheduling across all platforms</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon-small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3>Email Marketing</h3>
                <p>Create and send beautiful email campaigns</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon-small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3>Image Generation</h3>
                <p>AI-powered image creation for your content</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon-small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3>Landing Pages</h3>
                <p>Build high-converting landing pages</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon-small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </div>
                <h3>Website Chatbot</h3>
                <p>AI-powered chatbots for your website</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon-small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3>Competitor Analysis</h3>
                <p>Track and analyze your competitors</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon-small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3>Automation</h3>
                <p>Automate repetitive marketing tasks</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon-small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3>Brand Management</h3>
                <p>Manage multiple brands with ease</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Experience These Features?</h2>
            <p class="cta-description">
                Start your free trial today and see how MarketPulse can transform your marketing.
            </p>
            <div class="cta-actions">
                <a href="{{ route('register') }}" class="btn btn-white btn-lg">
                    Start Free Trial
                </a>
                <a href="{{ route('pricing') }}" class="btn btn-outline-white btn-lg">
                    View Pricing
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.features-hero {
    padding: 5rem 0 3rem;
    background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
}

.feature-section {
    padding: 5rem 0;
}

.feature-section.alternate {
    background: var(--gray-50);
}

.feature-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.feature-content.reverse {
    direction: rtl;
}

.feature-content.reverse > * {
    direction: ltr;
}

.feature-label {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: var(--primary);
    color: var(--white);
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.feature-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.feature-description {
    font-size: 1.125rem;
    color: var(--gray-600);
    line-height: 1.8;
    margin-bottom: 2rem;
}

.feature-points {
    list-style: none;
}

.feature-points li {
    padding: 0.75rem 0;
    padding-left: 2rem;
    position: relative;
    color: var(--gray-700);
}

.feature-points li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--success);
    font-weight: bold;
    font-size: 1.25rem;
}

.visual-placeholder {
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.visual-placeholder svg {
    width: 100%;
    height: auto;
}

.all-features {
    padding: 5rem 0;
    background: var(--white);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.feature-item {
    text-align: center;
    padding: 2rem;
}

.feature-icon-small {
    width: 48px;
    height: 48px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
}

.feature-icon-small svg {
    width: 24px;
    height: 24px;
}

.feature-item h3 {
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.feature-item p {
    color: var(--gray-600);
    font-size: 0.875rem;
}

@media (max-width: 1024px) {
    .feature-content {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
    
    .feature-title {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .features-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush


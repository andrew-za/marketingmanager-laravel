@extends('layouts.public')

@php
    $title = 'About Us';
@endphp

@section('content')
<!-- About Hero -->
<section class="about-hero">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">About MarketPulse</h1>
            <p class="section-description">
                We're on a mission to empower businesses with AI-powered marketing automation 
                that's both powerful and easy to use.
            </p>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="mission-section">
    <div class="container">
        <div class="mission-grid">
            <div class="mission-card">
                <div class="mission-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <h2>Our Mission</h2>
                <p>
                    To democratize advanced marketing automation and AI technology, making it accessible 
                    to businesses of all sizes. We believe every company deserves world-class marketing tools 
                    to grow and succeed in the digital age.
                </p>
            </div>

            <div class="mission-card">
                <div class="mission-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <h2>Our Vision</h2>
                <p>
                    To become the world's most trusted and innovative marketing automation platform, 
                    empowering millions of businesses to create meaningful connections with their audiences 
                    through intelligent, data-driven marketing.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Story Section -->
<section class="story-section">
    <div class="container">
        <div class="story-content">
            <div class="story-text">
                <h2 class="story-title">Our Story</h2>
                <p>
                    MarketPulse was born from a simple observation: marketing teams were drowning in tools, 
                    spending more time managing platforms than creating great content.
                </p>
                <p>
                    In 2023, our founders brought together decades of experience in marketing technology, 
                    AI, and user experience design to create a solution that would change the game. 
                    The result was MarketPulse – a comprehensive platform that combines powerful automation 
                    with intuitive design.
                </p>
                <p>
                    Today, we're proud to serve thousands of businesses worldwide, from ambitious startups 
                    to established enterprises. Every day, our platform helps marketers save time, 
                    create better content, and achieve remarkable results.
                </p>
            </div>
            <div class="story-stats">
                <div class="stat-card">
                    <div class="stat-number">10,000+</div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">500k+</div>
                    <div class="stat-label">Campaigns Launched</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Customer Satisfaction</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Countries Served</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our Values</h2>
            <p class="section-description">
                The principles that guide everything we do
            </p>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">🚀</div>
                <h3>Innovation First</h3>
                <p>
                    We constantly push boundaries to bring cutting-edge AI and automation 
                    features to our users.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">🤝</div>
                <h3>Customer Success</h3>
                <p>
                    Your success is our success. We're committed to helping you achieve 
                    your marketing goals.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">💡</div>
                <h3>Simplicity</h3>
                <p>
                    Powerful doesn't mean complicated. We make advanced features accessible 
                    and easy to use.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">🔒</div>
                <h3>Trust & Security</h3>
                <p>
                    We take your data security seriously and maintain the highest standards 
                    of privacy protection.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">🌍</div>
                <h3>Global Perspective</h3>
                <p>
                    Built for the world, we support businesses across cultures, languages, 
                    and time zones.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">⚡</div>
                <h3>Speed & Reliability</h3>
                <p>
                    Fast performance and 99.9% uptime ensure your marketing never stops.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Leadership Team</h2>
            <p class="section-description">
                Meet the people driving MarketPulse forward
            </p>
        </div>

        <div class="team-grid">
            <div class="team-member">
                <div class="member-avatar">JS</div>
                <h3 class="member-name">John Smith</h3>
                <p class="member-role">CEO & Co-Founder</p>
                <p class="member-bio">
                    Former VP of Marketing at TechCorp, 15+ years in marketing technology.
                </p>
            </div>

            <div class="team-member">
                <div class="member-avatar">SK</div>
                <h3 class="member-name">Sarah Kim</h3>
                <p class="member-role">CTO & Co-Founder</p>
                <p class="member-bio">
                    AI researcher and engineer with a passion for making AI accessible.
                </p>
            </div>

            <div class="team-member">
                <div class="member-avatar">MP</div>
                <h3 class="member-name">Michael Peters</h3>
                <p class="member-role">VP of Product</p>
                <p class="member-bio">
                    Product leader with experience at leading SaaS companies.
                </p>
            </div>

            <div class="team-member">
                <div class="member-avatar">EC</div>
                <h3 class="member-name">Emma Chen</h3>
                <p class="member-role">VP of Customer Success</p>
                <p class="member-bio">
                    Dedicated to ensuring every customer achieves their goals.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Join Thousands of Successful Marketers</h2>
            <p class="cta-description">
                Start your journey with MarketPulse today and transform your marketing.
            </p>
            <div class="cta-actions">
                <a href="{{ route('register') }}" class="btn btn-white btn-lg">
                    Start Free Trial
                </a>
                <a href="{{ route('contact') }}" class="btn btn-outline-white btn-lg">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.about-hero {
    padding: 5rem 0 3rem;
    background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
}

.mission-section {
    padding: 5rem 0;
    background: var(--white);
}

.mission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 3rem;
    max-width: 1000px;
    margin: 0 auto;
}

.mission-card {
    padding: 3rem;
    background: var(--gray-50);
    border-radius: 1rem;
    border: 1px solid var(--gray-200);
}

.mission-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    margin-bottom: 1.5rem;
}

.mission-icon svg {
    width: 32px;
    height: 32px;
}

.mission-card h2 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.mission-card p {
    color: var(--gray-600);
    line-height: 1.8;
    font-size: 1.125rem;
}

.story-section {
    padding: 5rem 0;
    background: var(--gray-50);
}

.story-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.story-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 2rem;
}

.story-text p {
    color: var(--gray-600);
    line-height: 1.8;
    margin-bottom: 1.5rem;
    font-size: 1.125rem;
}

.story-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.stat-card {
    padding: 2rem;
    background: var(--white);
    border-radius: 1rem;
    border: 1px solid var(--gray-200);
    text-align: center;
}

.stat-card .stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.stat-card .stat-label {
    color: var(--gray-600);
    font-size: 0.875rem;
}

.values-section {
    padding: 5rem 0;
    background: var(--white);
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.value-card {
    padding: 2rem;
    background: var(--gray-50);
    border-radius: 1rem;
    border: 1px solid var(--gray-200);
    text-align: center;
}

.value-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.value-card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.value-card p {
    color: var(--gray-600);
    line-height: 1.6;
}

.team-section {
    padding: 5rem 0;
    background: var(--gray-50);
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.team-member {
    text-align: center;
}

.member-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    margin: 0 auto 1.5rem;
}

.member-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.member-role {
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 1rem;
}

.member-bio {
    color: var(--gray-600);
    line-height: 1.6;
}

@media (max-width: 1024px) {
    .mission-grid {
        grid-template-columns: 1fr;
    }
    
    .story-content {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
}

@media (max-width: 768px) {
    .values-grid {
        grid-template-columns: 1fr;
    }
    
    .team-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush


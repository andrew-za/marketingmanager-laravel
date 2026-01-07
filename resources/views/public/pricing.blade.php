@extends('layouts.public')

@php
    $title = 'Pricing Plans';
@endphp

@section('content')
<!-- Pricing Hero -->
<section class="pricing-hero">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Simple, Transparent Pricing</h1>
            <p class="section-description">
                Choose the perfect plan for your business. All plans include a 14-day free trial.
            </p>
        </div>
    </div>
</section>

<!-- Pricing Plans -->
<section class="pricing-plans">
    <div class="container">
        <div class="pricing-grid">
            <!-- Starter Plan -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3 class="plan-name">Starter</h3>
                    <div class="plan-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount">29</span>
                        <span class="price-period">/month</span>
                    </div>
                    <p class="plan-description">Perfect for small businesses and startups</p>
                </div>
                <div class="pricing-features">
                    <ul class="feature-list">
                        <li><span class="check-icon">✓</span> Up to 5 social accounts</li>
                        <li><span class="check-icon">✓</span> 100 scheduled posts/month</li>
                        <li><span class="check-icon">✓</span> Basic analytics</li>
                        <li><span class="check-icon">✓</span> AI content generation (100 credits)</li>
                        <li><span class="check-icon">✓</span> Email support</li>
                        <li><span class="check-icon">✓</span> 1 brand profile</li>
                        <li><span class="check-icon">✓</span> Content calendar</li>
                    </ul>
                </div>
                <div class="pricing-action">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-block">Start Free Trial</a>
                </div>
            </div>

            <!-- Professional Plan -->
            <div class="pricing-card featured">
                <div class="popular-badge">Most Popular</div>
                <div class="pricing-header">
                    <h3 class="plan-name">Professional</h3>
                    <div class="plan-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount">79</span>
                        <span class="price-period">/month</span>
                    </div>
                    <p class="plan-description">For growing teams and businesses</p>
                </div>
                <div class="pricing-features">
                    <ul class="feature-list">
                        <li><span class="check-icon">✓</span> Up to 20 social accounts</li>
                        <li><span class="check-icon">✓</span> Unlimited scheduled posts</li>
                        <li><span class="check-icon">✓</span> Advanced analytics & reports</li>
                        <li><span class="check-icon">✓</span> AI content generation (500 credits)</li>
                        <li><span class="check-icon">✓</span> Priority email & chat support</li>
                        <li><span class="check-icon">✓</span> 5 brand profiles</li>
                        <li><span class="check-icon">✓</span> Team collaboration (10 members)</li>
                        <li><span class="check-icon">✓</span> Approval workflows</li>
                        <li><span class="check-icon">✓</span> Email marketing (5,000 contacts)</li>
                        <li><span class="check-icon">✓</span> Landing page builder</li>
                    </ul>
                </div>
                <div class="pricing-action">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-block">Start Free Trial</a>
                </div>
            </div>

            <!-- Enterprise Plan -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3 class="plan-name">Enterprise</h3>
                    <div class="plan-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount">199</span>
                        <span class="price-period">/month</span>
                    </div>
                    <p class="plan-description">For large organizations and agencies</p>
                </div>
                <div class="pricing-features">
                    <ul class="feature-list">
                        <li><span class="check-icon">✓</span> Unlimited social accounts</li>
                        <li><span class="check-icon">✓</span> Unlimited scheduled posts</li>
                        <li><span class="check-icon">✓</span> Custom analytics & white-label reports</li>
                        <li><span class="check-icon">✓</span> AI content generation (Unlimited)</li>
                        <li><span class="check-icon">✓</span> Dedicated account manager</li>
                        <li><span class="check-icon">✓</span> Unlimited brand profiles</li>
                        <li><span class="check-icon">✓</span> Unlimited team members</li>
                        <li><span class="check-icon">✓</span> Advanced approval workflows</li>
                        <li><span class="check-icon">✓</span> Email marketing (Unlimited)</li>
                        <li><span class="check-icon">✓</span> Custom integrations</li>
                        <li><span class="check-icon">✓</span> API access</li>
                        <li><span class="check-icon">✓</span> SLA guarantee</li>
                    </ul>
                </div>
                <div class="pricing-action">
                    <a href="{{ route('contact') }}" class="btn btn-primary btn-block">Contact Sales</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Frequently Asked Questions</h2>
        </div>

        <div class="faq-grid">
            <div class="faq-item">
                <h3 class="faq-question">Can I change plans later?</h3>
                <p class="faq-answer">
                    Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately, 
                    and we'll prorate your billing accordingly.
                </p>
            </div>

            <div class="faq-item">
                <h3 class="faq-question">What payment methods do you accept?</h3>
                <p class="faq-answer">
                    We accept all major credit cards (Visa, MasterCard, American Express) and PayPal. 
                    Enterprise customers can also pay via invoice.
                </p>
            </div>

            <div class="faq-item">
                <h3 class="faq-question">Is there a free trial?</h3>
                <p class="faq-answer">
                    Yes! All plans come with a 14-day free trial. No credit card required to start. 
                    You can explore all features before committing.
                </p>
            </div>

            <div class="faq-item">
                <h3 class="faq-question">Can I cancel anytime?</h3>
                <p class="faq-answer">
                    Absolutely. You can cancel your subscription at any time. Your account will remain 
                    active until the end of your billing period.
                </p>
            </div>

            <div class="faq-item">
                <h3 class="faq-question">Do you offer discounts for annual billing?</h3>
                <p class="faq-answer">
                    Yes! Save 20% when you pay annually. Annual plans also include priority support and 
                    early access to new features.
                </p>
            </div>

            <div class="faq-item">
                <h3 class="faq-question">What happens to my data if I cancel?</h3>
                <p class="faq-answer">
                    Your data is yours. You can export all your content and analytics before canceling. 
                    We keep your data for 30 days after cancellation in case you change your mind.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Get Started?</h2>
            <p class="cta-description">
                Start your 14-day free trial today. No credit card required.
            </p>
            <div class="cta-actions">
                <a href="{{ route('register') }}" class="btn btn-white btn-lg">
                    Start Free Trial
                </a>
                <a href="{{ route('contact') }}" class="btn btn-outline-white btn-lg">
                    Contact Sales
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.pricing-hero {
    padding: 4rem 0 2rem;
    background: var(--gray-50);
}

.pricing-plans {
    padding: 3rem 0 5rem;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.pricing-card {
    background: var(--white);
    border: 2px solid var(--gray-200);
    border-radius: 1rem;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 0.3s;
}

.pricing-card:hover {
    border-color: var(--primary);
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(79, 70, 229, 0.15);
}

.pricing-card.featured {
    border-color: var(--primary);
    box-shadow: 0 20px 40px rgba(79, 70, 229, 0.15);
}

.popular-badge {
    position: absolute;
    top: -12px;
    right: 2rem;
    background: var(--primary);
    color: var(--white);
    padding: 0.25rem 1rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.plan-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.plan-price {
    display: flex;
    align-items: baseline;
    margin-bottom: 1rem;
}

.price-currency {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-600);
}

.price-amount {
    font-size: 3.5rem;
    font-weight: 800;
    color: var(--gray-900);
}

.price-period {
    font-size: 1rem;
    color: var(--gray-600);
    margin-left: 0.25rem;
}

.plan-description {
    color: var(--gray-600);
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--gray-200);
}

.pricing-features {
    flex: 1;
    margin-bottom: 2rem;
}

.feature-list {
    list-style: none;
}

.feature-list li {
    padding: 0.75rem 0;
    color: var(--gray-700);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.check-icon {
    color: var(--success);
    font-weight: bold;
    font-size: 1.25rem;
}

.btn-block {
    width: 100%;
}

.faq-section {
    padding: 5rem 0;
    background: var(--gray-50);
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    max-width: 1000px;
    margin: 0 auto;
}

.faq-item {
    background: var(--white);
    padding: 2rem;
    border-radius: 1rem;
    border: 1px solid var(--gray-200);
}

.faq-question {
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--gray-900);
}

.faq-answer {
    color: var(--gray-600);
    line-height: 1.6;
}

@media (max-width: 768px) {
    .pricing-grid {
        grid-template-columns: 1fr;
    }
    
    .faq-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush



@extends('layouts.public')

@php
    $title = 'Contact Us';
@endphp

@section('content')
<!-- Contact Hero -->
<section class="contact-hero">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Get in Touch</h1>
            <p class="section-description">
                Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
            </p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <h2 class="form-title">Send us a Message</h2>
                <form class="contact-form" action="#" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="company" class="form-label">Company</label>
                        <input type="text" id="company" name="company" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <select id="subject" name="subject" class="form-input" required>
                            <option value="">Select a subject</option>
                            <option value="sales">Sales Inquiry</option>
                            <option value="support">Technical Support</option>
                            <option value="partnership">Partnership</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" rows="5" class="form-input" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Send Message
                    </button>

                    <p class="form-note">
                        We'll get back to you within 24 hours.
                    </p>
                </form>
            </div>

            <!-- Contact Information -->
            <div class="contact-info-wrapper">
                <div class="contact-info-card">
                    <h2 class="info-title">Contact Information</h2>
                    <p class="info-description">
                        Get in touch with us through any of these channels.
                    </p>

                    <div class="info-items">
                        <div class="info-item">
                            <div class="info-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="info-label">Email</h3>
                                <p class="info-value">support@marketpulse.com</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="info-label">Phone</h3>
                                <p class="info-value">+1 (555) 123-4567</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="info-label">Office</h3>
                                <p class="info-value">123 Marketing Street<br>San Francisco, CA 94102</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="info-label">Business Hours</h3>
                                <p class="info-value">Monday - Friday: 9am - 6pm PST<br>Saturday - Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="support-card">
                    <h3 class="support-title">Looking for Support?</h3>
                    <p class="support-description">
                        Visit our Help Center for instant answers to common questions, tutorials, and guides.
                    </p>
                    <a href="#" class="btn btn-secondary btn-block">
                        Visit Help Center
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section (Placeholder) -->
<section class="map-section">
    <div class="map-placeholder">
        <div class="map-overlay">
            <div class="map-content">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="map-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <p>San Francisco, CA</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="contact-faq">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Frequently Asked Questions</h2>
        </div>

        <div class="faq-grid">
            <div class="faq-item">
                <h3 class="faq-question">How quickly will I get a response?</h3>
                <p class="faq-answer">
                    We typically respond to all inquiries within 24 hours during business days. 
                    Priority support customers receive responses within 4 hours.
                </p>
            </div>

            <div class="faq-item">
                <h3 class="faq-question">Can I schedule a demo?</h3>
                <p class="faq-answer">
                    Yes! Select "Sales Inquiry" in the form above and mention you'd like a demo. 
                    Our team will reach out to schedule a personalized walkthrough.
                </p>
            </div>

            <div class="faq-item">
                <h3 class="faq-question">Do you offer phone support?</h3>
                <p class="faq-answer">
                    Phone support is available for Professional and Enterprise plan customers. 
                    All customers have access to email and chat support.
                </p>
            </div>

            <div class="faq-item">
                <h3 class="faq-question">Where can I find technical documentation?</h3>
                <p class="faq-answer">
                    Our comprehensive documentation, API references, and tutorials are available 
                    in the Help Center and Developer Portal.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.contact-hero {
    padding: 5rem 0 3rem;
    background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
}

.contact-section {
    padding: 5rem 0;
    background: var(--white);
}

.contact-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 4rem;
}

.form-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 2rem;
}

.contact-form {
    background: var(--gray-50);
    padding: 2rem;
    border-radius: 1rem;
    border: 1px solid var(--gray-200);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--gray-700);
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.2s;
    background: var(--white);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

textarea.form-input {
    resize: vertical;
    font-family: inherit;
}

.btn-block {
    width: 100%;
}

.form-note {
    text-align: center;
    color: var(--gray-600);
    font-size: 0.875rem;
    margin-top: 1rem;
}

.contact-info-card {
    background: var(--gray-50);
    padding: 2rem;
    border-radius: 1rem;
    border: 1px solid var(--gray-200);
    margin-bottom: 2rem;
}

.info-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.info-description {
    color: var(--gray-600);
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--gray-200);
}

.info-items {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.info-item {
    display: flex;
    gap: 1rem;
}

.info-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;
    background: var(--primary);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
}

.info-icon svg {
    width: 24px;
    height: 24px;
}

.info-label {
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.info-value {
    color: var(--gray-600);
    line-height: 1.6;
}

.support-card {
    background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
    padding: 2rem;
    border-radius: 1rem;
    color: var(--white);
}

.support-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.support-description {
    margin-bottom: 1.5rem;
    opacity: 0.9;
}

.map-section {
    height: 400px;
    background: var(--gray-100);
}

.map-placeholder {
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
}

.map-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.8);
}

.map-content {
    text-align: center;
}

.map-icon {
    width: 64px;
    height: 64px;
    color: var(--primary);
    margin: 0 auto 1rem;
}

.map-content p {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-700);
}

.contact-faq {
    padding: 5rem 0;
    background: var(--gray-50);
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
    margin-bottom: 0.75rem;
    color: var(--gray-900);
}

.faq-answer {
    color: var(--gray-600);
    line-height: 1.6;
}

@media (max-width: 1024px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .faq-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush



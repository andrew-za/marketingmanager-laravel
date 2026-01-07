@extends('layouts.public')

@php
    $title = '404 - Page Not Found';
@endphp

@section('content')
<section class="error-section">
    <div class="container">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-description">
                Sorry, we couldn't find the page you're looking for. 
                Perhaps you mistyped the URL or the page has been moved.
            </p>
            <div class="error-actions">
                <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                    Go to Homepage
                </a>
                <a href="{{ route('contact') }}" class="btn btn-secondary btn-lg">
                    Contact Support
                </a>
            </div>
            
            <div class="error-links">
                <p class="error-links-title">Popular Pages:</p>
                <div class="error-links-grid">
                    <a href="{{ route('features') }}">Features</a>
                    <a href="{{ route('pricing') }}">Pricing</a>
                    <a href="{{ route('about') }}">About Us</a>
                    <a href="{{ route('login') }}">Login</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.error-section {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    padding: 5rem 0;
    background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
}

.error-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.error-code {
    font-size: 8rem;
    font-weight: 900;
    background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 50%, #ec4899 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
    margin-bottom: 1rem;
}

.error-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

.error-description {
    font-size: 1.125rem;
    color: var(--gray-600);
    line-height: 1.8;
    margin-bottom: 2.5rem;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 3rem;
}

.error-links {
    padding-top: 2rem;
    border-top: 1px solid var(--gray-200);
}

.error-links-title {
    color: var(--gray-600);
    margin-bottom: 1rem;
    font-weight: 600;
}

.error-links-grid {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.error-links-grid a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.error-links-grid a:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

@media (max-width: 768px) {
    .error-code {
        font-size: 5rem;
    }
    
    .error-title {
        font-size: 1.75rem;
    }
    
    .error-actions {
        flex-direction: column;
    }
    
    .error-links-grid {
        flex-direction: column;
        gap: 0.75rem;
    }
}
</style>
@endpush


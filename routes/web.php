<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Campaign\CampaignController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\LocaleController;

// Locale switching routes
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/locale/regional/{regionalLocale}', [LocaleController::class, 'switchRegional'])->name('locale.switch.regional');

// Public Marketing Pages
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/features', [PublicController::class, 'features'])->name('features');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');

Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('/signup', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/signup', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
});

Route::prefix('admin')->middleware('guest:admin')->group(function () {
    Route::get('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'login']);
});

Route::middleware(['auth', 'verified'])->prefix('main')->name('main.')->group(function () {
    Route::get('/organizations', [App\Http\Controllers\OrganizationController::class, 'index'])->name('organizations');
    Route::get('/onboarding', [App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding');
    
    Route::middleware('organization')->prefix('{organizationId}')->group(function () {
        Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/collaboration', [App\Http\Controllers\CollaborationController::class, 'index'])->name('collaboration');
        
        Route::prefix('campaigns')->name('campaigns.')->group(function () {
            Route::get('/', [CampaignController::class, 'index'])->name('index');
            Route::get('/create', [CampaignController::class, 'create'])->name('create');
            Route::post('/', [CampaignController::class, 'store'])->name('store');
            Route::get('/{campaign}', [CampaignController::class, 'show'])->name('show');
            Route::put('/{campaign}', [CampaignController::class, 'update'])->name('update');
            Route::delete('/{campaign}', [CampaignController::class, 'destroy'])->name('destroy');
            Route::post('/{campaign}/publish', [CampaignController::class, 'publish'])->name('publish');
        });
    });
});

Route::prefix('admin')->middleware(['auth:admin', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('organizations', App\Http\Controllers\Admin\OrganizationController::class);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
});

Route::middleware(['auth', 'role:agency'])->prefix('agency')->name('agency.')->group(function () {
    Route::middleware('agency')->prefix('{agencyId}')->group(function () {
        Route::get('/', [App\Http\Controllers\Agency\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/clients', [App\Http\Controllers\Agency\ClientController::class, 'index'])->name('clients');
    });
});


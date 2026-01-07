<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\AiGenerationController;
use App\Http\Controllers\Api\SocialMediaController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\EmailMarketingController;
use App\Http\Controllers\Api\BrandsProductsController;
use App\Http\Controllers\Api\TasksProjectsController;
use App\Http\Controllers\Campaign\CampaignController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API endpoints
Route::get('locales/available', [\App\Http\Controllers\LocaleController::class, 'available'])->name('api.locales.available');

// Authentication endpoints (public)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('api.auth.forgot-password');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('api.auth.reset-password');
});

// Protected API endpoints (v1)
Route::prefix('v1')->middleware(['auth:sanctum', 'api.rate_limit'])->group(function () {
    
    // Authentication endpoints (protected)
    Route::prefix('auth')->group(function () {
        Route::get('user', [AuthController::class, 'user'])->name('api.auth.user');
        Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
    });

    // Organizations
    Route::apiResource('organizations', OrganizationController::class);

    // Campaigns
    Route::prefix('campaigns')->group(function () {
        Route::get('/', [CampaignController::class, 'index'])->middleware('permission:campaigns.view')->name('api.campaigns.index');
        Route::post('/', [CampaignController::class, 'store'])->middleware('permission:campaigns.create')->name('api.campaigns.store');
        Route::get('{campaign}', [CampaignController::class, 'show'])->middleware('permission:campaigns.view')->name('api.campaigns.show');
        Route::put('{campaign}', [CampaignController::class, 'update'])->middleware('permission:campaigns.update')->name('api.campaigns.update');
        Route::delete('{campaign}', [CampaignController::class, 'destroy'])->middleware(['permission:campaigns.delete', 'require.confirmation'])->name('api.campaigns.destroy');
        Route::post('{campaign}/publish', [CampaignController::class, 'publish'])->middleware('permission:campaigns.publish')->name('api.campaigns.publish');
    });

    // Content
    Route::prefix('content')->group(function () {
        Route::get('/', [ContentController::class, 'index'])->middleware('permission:content.view')->name('api.content.index');
        Route::get('{content}', [ContentController::class, 'show'])->middleware('permission:content.view')->name('api.content.show');
        Route::post('{content}/approve', [ContentController::class, 'approve'])->middleware('permission:content.approve')->name('api.content.approve');
        Route::post('{content}/reject', [ContentController::class, 'reject'])->middleware('permission:content.approve')->name('api.content.reject');
    });

    // AI Generation
    Route::prefix('ai')->group(function () {
        Route::post('generate/social-post', [AiGenerationController::class, 'generateSocialPost'])->middleware('permission:ai.generate')->name('api.ai.generate.social-post');
        Route::post('generate/press-release', [AiGenerationController::class, 'generatePressRelease'])->middleware('permission:ai.generate')->name('api.ai.generate.press-release');
        Route::post('generate/email', [AiGenerationController::class, 'generateEmail'])->middleware('permission:ai.generate')->name('api.ai.generate.email');
        Route::post('generate/image', [AiGenerationController::class, 'generateImage'])->middleware('permission:ai.generate')->name('api.ai.generate.image');
        Route::post('analyze/sentiment', [AiGenerationController::class, 'analyzeSentiment'])->middleware('permission:ai.analyze')->name('api.ai.analyze.sentiment');
        Route::post('analyze/seo', [AiGenerationController::class, 'analyzeSeo'])->middleware('permission:ai.analyze')->name('api.ai.analyze.seo');
    });

    // Social Media
    Route::prefix('social')->group(function () {
        Route::get('connections', [SocialMediaController::class, 'connections'])->middleware('permission:social.view')->name('api.social.connections');
        Route::post('connections', [SocialMediaController::class, 'storeConnection'])->middleware('permission:social.create')->name('api.social.connections.store');
        Route::delete('connections/{connection}', [SocialMediaController::class, 'deleteConnection'])->middleware('permission:social.delete')->name('api.social.connections.delete');
        Route::post('publish', [SocialMediaController::class, 'publish'])->middleware('permission:social.publish')->name('api.social.publish');
        Route::get('analytics', [SocialMediaController::class, 'analytics'])->middleware('permission:social.view')->name('api.social.analytics');
    });

    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('dashboard', [AnalyticsController::class, 'dashboard'])->middleware('permission:analytics.view')->name('api.analytics.dashboard');
        Route::get('campaigns/{campaignId}', [AnalyticsController::class, 'campaign'])->middleware('permission:analytics.view')->name('api.analytics.campaign');
        Route::get('reports', [AnalyticsController::class, 'reports'])->middleware('permission:analytics.view')->name('api.analytics.reports');
        Route::post('reports', [AnalyticsController::class, 'createReport'])->middleware('permission:analytics.create')->name('api.analytics.reports.store');
        Route::get('reports/{reportId}', [AnalyticsController::class, 'getReport'])->middleware('permission:analytics.view')->name('api.analytics.reports.show');
    });

    // Email Marketing
    Route::prefix('email-marketing')->group(function () {
        Route::get('campaigns', [EmailMarketingController::class, 'campaigns'])->middleware('permission:email.view')->name('api.email.campaigns.index');
        Route::post('campaigns', [EmailMarketingController::class, 'createCampaign'])->middleware('permission:email.create')->name('api.email.campaigns.store');
        Route::get('campaigns/{campaignId}', [EmailMarketingController::class, 'getCampaign'])->middleware('permission:email.view')->name('api.email.campaigns.show');
        Route::put('campaigns/{campaignId}', [EmailMarketingController::class, 'updateCampaign'])->middleware('permission:email.update')->name('api.email.campaigns.update');
        Route::delete('campaigns/{campaignId}', [EmailMarketingController::class, 'deleteCampaign'])->middleware('permission:email.delete')->name('api.email.campaigns.destroy');
    });

    // Brands & Products
    Route::prefix('brands-products')->group(function () {
        // Brands
        Route::get('brands', [BrandsProductsController::class, 'brands'])->middleware('permission:brands.view')->name('api.brands.index');
        Route::post('brands', [BrandsProductsController::class, 'createBrand'])->middleware('permission:brands.create')->name('api.brands.store');
        Route::get('brands/{brandId}', [BrandsProductsController::class, 'getBrand'])->middleware('permission:brands.view')->name('api.brands.show');
        Route::put('brands/{brandId}', [BrandsProductsController::class, 'updateBrand'])->middleware('permission:brands.update')->name('api.brands.update');
        Route::delete('brands/{brandId}', [BrandsProductsController::class, 'deleteBrand'])->middleware('permission:brands.delete')->name('api.brands.destroy');
        
        // Products
        Route::get('products', [BrandsProductsController::class, 'products'])->middleware('permission:products.view')->name('api.products.index');
        Route::post('products', [BrandsProductsController::class, 'createProduct'])->middleware('permission:products.create')->name('api.products.store');
        Route::get('products/{productId}', [BrandsProductsController::class, 'getProduct'])->middleware('permission:products.view')->name('api.products.show');
        Route::put('products/{productId}', [BrandsProductsController::class, 'updateProduct'])->middleware('permission:products.update')->name('api.products.update');
        Route::delete('products/{productId}', [BrandsProductsController::class, 'deleteProduct'])->middleware('permission:products.delete')->name('api.products.destroy');
    });

    // Tasks & Projects
    Route::prefix('tasks-projects')->group(function () {
        // Tasks
        Route::get('tasks', [TasksProjectsController::class, 'tasks'])->middleware('permission:tasks.view')->name('api.tasks.index');
        Route::post('tasks', [TasksProjectsController::class, 'createTask'])->middleware('permission:tasks.create')->name('api.tasks.store');
        Route::get('tasks/{taskId}', [TasksProjectsController::class, 'getTask'])->middleware('permission:tasks.view')->name('api.tasks.show');
        Route::put('tasks/{taskId}', [TasksProjectsController::class, 'updateTask'])->middleware('permission:tasks.update')->name('api.tasks.update');
        Route::delete('tasks/{taskId}', [TasksProjectsController::class, 'deleteTask'])->middleware('permission:tasks.delete')->name('api.tasks.destroy');
        
        // Projects
        Route::get('projects', [TasksProjectsController::class, 'projects'])->middleware('permission:projects.view')->name('api.projects.index');
        Route::post('projects', [TasksProjectsController::class, 'createProject'])->middleware('permission:projects.create')->name('api.projects.store');
        Route::get('projects/{projectId}', [TasksProjectsController::class, 'getProject'])->middleware('permission:projects.view')->name('api.projects.show');
        Route::put('projects/{projectId}', [TasksProjectsController::class, 'updateProject'])->middleware('permission:projects.update')->name('api.projects.update');
        Route::delete('projects/{projectId}', [TasksProjectsController::class, 'deleteProject'])->middleware('permission:projects.delete')->name('api.projects.destroy');
    });
});

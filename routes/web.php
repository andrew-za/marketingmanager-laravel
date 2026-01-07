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
    
    // Password reset routes
    Route::get('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::prefix('admin')->middleware('guest:admin')->group(function () {
    Route::get('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'login']);
});

// Email verification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Two-factor authentication routes
Route::middleware(['auth'])->prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('/setup', [App\Http\Controllers\Auth\TwoFactorController::class, 'showSetupForm'])->name('setup');
    Route::post('/enable', [App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('enable');
    Route::post('/disable', [App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('disable');
});

Route::middleware(['guest'])->prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('/challenge', [App\Http\Controllers\Auth\TwoFactorController::class, 'showChallengeForm'])->name('challenge');
    Route::post('/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'verifyChallenge'])->name('verify');
});

// Profile routes
Route::middleware(['auth', 'verified'])->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [App\Http\Controllers\ProfileController::class, 'show'])->name('show');
    Route::put('/update', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('update');
    Route::put('/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password.update');
    Route::put('/avatar', [App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('avatar.update');
    Route::get('/security', [App\Http\Controllers\ProfileController::class, 'showSecurity'])->name('security');
    Route::delete('/sessions/{sessionId}', [App\Http\Controllers\ProfileController::class, 'revokeSession'])->name('sessions.revoke');
    Route::post('/sessions/revoke-other', [App\Http\Controllers\ProfileController::class, 'revokeOtherSessions'])->name('sessions.revoke-other');
});

Route::middleware(['auth', 'verified'])->prefix('main')->name('main.')->group(function () {
    Route::get('/organizations', [App\Http\Controllers\OrganizationController::class, 'index'])->name('organizations');
    Route::get('/onboarding', [App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding');
    
    Route::middleware(['tenant', 'organization'])->prefix('{organizationId}')->group(function () {
        Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        
        Route::prefix('collaboration')->name('collaboration.')->group(function () {
            Route::get('/', [App\Http\Controllers\CollaborationController::class, 'index'])->name('index');
            Route::post('/topics', [App\Http\Controllers\CollaborationController::class, 'createTopic'])->name('topics.create');
            Route::get('/topics/{topic}', [App\Http\Controllers\CollaborationController::class, 'showTopic'])->name('topics.show');
            Route::post('/topics/{topic}/messages', [App\Http\Controllers\CollaborationController::class, 'storeMessage'])->name('messages.store');
            
            // Chat Reactions
            Route::prefix('messages/{chatMessage}')->name('messages.')->group(function () {
                Route::post('/reactions', [App\Http\Controllers\Chat\ChatReactionController::class, 'addReaction'])->name('reactions.add');
                Route::delete('/reactions/{reaction}', [App\Http\Controllers\Chat\ChatReactionController::class, 'removeReaction'])->name('reactions.remove');
                Route::get('/reactions', [App\Http\Controllers\Chat\ChatReactionController::class, 'getReactions'])->name('reactions.index');
            });
        });
        
        // Review (Content Approval)
        Route::middleware('brand.context')->prefix('review')->name('review.')->group(function () {
            Route::get('/', [App\Http\Controllers\ContentApprovalController::class, 'index'])->name('index');
            Route::get('/{approval}', [App\Http\Controllers\ContentApprovalController::class, 'show'])->name('show');
            Route::post('/{approval}/approve', [App\Http\Controllers\ContentApprovalController::class, 'approve'])->name('approve');
            Route::post('/{approval}/reject', [App\Http\Controllers\ContentApprovalController::class, 'reject'])->name('reject');
            Route::get('/scheduled-posts/{scheduledPost}/history', [App\Http\Controllers\ContentApprovalController::class, 'getApprovalHistory'])->name('history');
        });
        
        // Tasks
        Route::middleware('not.client')->prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [App\Http\Controllers\Task\TaskController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Task\TaskController::class, 'store'])->name('store');
            Route::get('/{task}', [App\Http\Controllers\Task\TaskController::class, 'show'])->name('show');
            Route::put('/{task}', [App\Http\Controllers\Task\TaskController::class, 'update'])->name('update');
            Route::delete('/{task}', [App\Http\Controllers\Task\TaskController::class, 'destroy'])->name('destroy');
            Route::post('/{task}/assign', [App\Http\Controllers\Task\TaskController::class, 'assign'])->name('assign');
            Route::post('/{task}/status', [App\Http\Controllers\Task\TaskController::class, 'updateStatus'])->name('status');
            Route::post('/{task}/comments', [App\Http\Controllers\Task\TaskController::class, 'addComment'])->name('comments.store');
            Route::post('/{task}/attachments', [App\Http\Controllers\Task\TaskController::class, 'addAttachment'])->name('attachments.store');
            Route::delete('/attachments/{taskAttachment}', [App\Http\Controllers\Task\TaskController::class, 'deleteAttachment'])->name('attachments.destroy');
            
            // Task Templates
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [App\Http\Controllers\Task\TaskTemplateController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\Task\TaskTemplateController::class, 'store'])->name('store');
                Route::get('/{taskTemplate}', [App\Http\Controllers\Task\TaskTemplateController::class, 'show'])->name('show');
                Route::put('/{taskTemplate}', [App\Http\Controllers\Task\TaskTemplateController::class, 'update'])->name('update');
                Route::delete('/{taskTemplate}', [App\Http\Controllers\Task\TaskTemplateController::class, 'destroy'])->name('destroy');
                Route::post('/{taskTemplate}/create-task', [App\Http\Controllers\Task\TaskTemplateController::class, 'createTask'])->name('create-task');
            });
        });
        
        // Projects
        Route::middleware('not.client')->prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [App\Http\Controllers\Project\ProjectController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Project\ProjectController::class, 'store'])->name('store');
            Route::get('/{project}', [App\Http\Controllers\Project\ProjectController::class, 'show'])->name('show');
            Route::put('/{project}', [App\Http\Controllers\Project\ProjectController::class, 'update'])->name('update');
            Route::delete('/{project}', [App\Http\Controllers\Project\ProjectController::class, 'destroy'])->name('destroy');
            Route::post('/{project}/members', [App\Http\Controllers\Project\ProjectController::class, 'addMember'])->name('members.store');
            Route::delete('/{project}/members/{userId}', [App\Http\Controllers\Project\ProjectController::class, 'removeMember'])->name('members.destroy');
            Route::put('/{project}/members/{userId}/role', [App\Http\Controllers\Project\ProjectController::class, 'updateMemberRole'])->name('members.role');
            Route::post('/{project}/status', [App\Http\Controllers\Project\ProjectController::class, 'updateStatus'])->name('status');
            
            // Project Templates
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [App\Http\Controllers\Project\ProjectTemplateController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\Project\ProjectTemplateController::class, 'store'])->name('store');
                Route::get('/{projectTemplate}', [App\Http\Controllers\Project\ProjectTemplateController::class, 'show'])->name('show');
                Route::put('/{projectTemplate}', [App\Http\Controllers\Project\ProjectTemplateController::class, 'update'])->name('update');
                Route::delete('/{projectTemplate}', [App\Http\Controllers\Project\ProjectTemplateController::class, 'destroy'])->name('destroy');
                Route::post('/{projectTemplate}/create-project', [App\Http\Controllers\Project\ProjectTemplateController::class, 'createProject'])->name('create-project');
            });
        });
        
        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\Notification\NotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [App\Http\Controllers\Notification\NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/{notification}/read', [App\Http\Controllers\Notification\NotificationController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [App\Http\Controllers\Notification\NotificationController::class, 'markAllAsRead'])->name('read-all');
            Route::delete('/{notification}', [App\Http\Controllers\Notification\NotificationController::class, 'destroy'])->name('destroy');
            Route::get('/preferences', [App\Http\Controllers\Notification\NotificationController::class, 'preferences'])->name('preferences');
            Route::put('/preferences', [App\Http\Controllers\Notification\NotificationController::class, 'updatePreference'])->name('preferences.update');
        });
        
        Route::middleware('not.client')->prefix('campaigns')->name('campaigns.')->group(function () {
            Route::get('/', [CampaignController::class, 'index'])->name('index');
            Route::get('/create', [CampaignController::class, 'create'])->name('create');
            Route::post('/', [CampaignController::class, 'store'])->name('store');
            Route::post('/generate-plan', [CampaignController::class, 'generatePlan'])->name('generate-plan');
            Route::post('/ai/suggestions', [CampaignController::class, 'getAISuggestions'])->name('ai.suggestions');
            Route::get('/{campaign}', [CampaignController::class, 'show'])->name('show');
            Route::put('/{campaign}', [CampaignController::class, 'update'])->name('update');
            Route::delete('/{campaign}', [CampaignController::class, 'destroy'])->name('destroy');
            Route::post('/{campaign}/submit-for-review', [CampaignController::class, 'submitForReview'])->name('submit-for-review');
            Route::post('/{campaign}/generate-content', [CampaignController::class, 'generateContent'])->name('generate-content');
            Route::post('/{campaign}/publish', [CampaignController::class, 'publish'])->name('publish');
            Route::post('/{campaign}/pause', [CampaignController::class, 'pause'])->name('pause');
            Route::post('/{campaign}/resume', [CampaignController::class, 'resume'])->name('resume');
            Route::post('/{campaign}/complete', [CampaignController::class, 'complete'])->name('complete');
            Route::post('/{campaign}/deactivate', [CampaignController::class, 'deactivate'])->name('deactivate');
            Route::post('/{campaign}/reactivate', [CampaignController::class, 'reactivate'])->name('reactivate');
            Route::post('/{campaign}/clone', [CampaignController::class, 'clone'])->name('clone');
            Route::post('/{campaign}/products', [CampaignController::class, 'attachProducts'])->name('attach-products');
            Route::delete('/{campaign}/products', [CampaignController::class, 'detachProducts'])->name('detach-products');
            
            // Content Management
            Route::prefix('{campaign}/content')->name('content.')->group(function () {
                Route::get('/', [App\Http\Controllers\Campaign\ContentManagementController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\Campaign\ContentManagementController::class, 'store'])->name('store');
                Route::put('/{scheduledPost}', [App\Http\Controllers\Campaign\ContentManagementController::class, 'update'])->name('update');
                Route::delete('/{scheduledPost}', [App\Http\Controllers\Campaign\ContentManagementController::class, 'destroy'])->name('destroy');
            });
            
            // Competition
            Route::prefix('{campaign}/competitions')->name('competitions.')->group(function () {
                Route::get('/', [App\Http\Controllers\Campaign\CompetitionController::class, 'index'])->name('index');
                Route::post('/attach', [App\Http\Controllers\Campaign\CompetitionController::class, 'attachCompetitor'])->name('attach');
                Route::delete('/{competitor}', [App\Http\Controllers\Campaign\CompetitionController::class, 'detachCompetitor'])->name('detach');
            });
        });
        
        // Campaign Templates
        Route::prefix('campaign-templates')->name('campaign-templates.')->group(function () {
            Route::get('/', [App\Http\Controllers\Campaign\CampaignTemplateController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Campaign\CampaignTemplateController::class, 'store'])->name('store');
            Route::get('/{template}', [App\Http\Controllers\Campaign\CampaignTemplateController::class, 'show'])->name('show');
            Route::put('/{template}', [App\Http\Controllers\Campaign\CampaignTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [App\Http\Controllers\Campaign\CampaignTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{template}/create-campaign', [App\Http\Controllers\Campaign\CampaignTemplateController::class, 'createCampaignFromTemplate'])->name('create-campaign');
        });
        
        // Paid Campaigns
        Route::prefix('paid-campaigns')->name('paid-campaigns.')->group(function () {
            Route::get('/', [App\Http\Controllers\Campaign\PaidCampaignController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Campaign\PaidCampaignController::class, 'store'])->name('store');
            Route::get('/{paidCampaign}', [App\Http\Controllers\Campaign\PaidCampaignController::class, 'show'])->name('show');
            Route::put('/{paidCampaign}', [App\Http\Controllers\Campaign\PaidCampaignController::class, 'update'])->name('update');
            Route::delete('/{paidCampaign}', [App\Http\Controllers\Campaign\PaidCampaignController::class, 'destroy'])->name('destroy');
            Route::post('/{paidCampaign}/metrics', [App\Http\Controllers\Campaign\PaidCampaignController::class, 'updateMetrics'])->name('metrics');
        });
        
        // Content Calendar
        Route::prefix('content-calendar')->name('content-calendar.')->group(function () {
            Route::get('/', [App\Http\Controllers\ContentCalendarController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\ContentCalendarController::class, 'store'])->name('store');
            Route::put('/{event}', [App\Http\Controllers\ContentCalendarController::class, 'update'])->name('update');
            Route::delete('/{event}', [App\Http\Controllers\ContentCalendarController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-schedule', [App\Http\Controllers\ContentCalendarController::class, 'bulkSchedule'])->name('bulk-schedule');
            Route::post('/recurring', [App\Http\Controllers\ContentCalendarController::class, 'createRecurringSchedule'])->name('recurring');
            Route::delete('/recurring/{scheduledPost}', [App\Http\Controllers\ContentCalendarController::class, 'cancelRecurringSchedule'])->name('cancel-recurring');
        });
        
        // Content Approval
        Route::prefix('content-approvals')->name('content-approvals.')->group(function () {
            Route::get('/', [App\Http\Controllers\ContentApprovalController::class, 'index'])->name('index');
            Route::get('/{approval}', [App\Http\Controllers\ContentApprovalController::class, 'show'])->name('show');
            Route::post('/{scheduledPost}/request', [App\Http\Controllers\ContentApprovalController::class, 'requestApproval'])->name('request');
            Route::post('/{approval}/approve', [App\Http\Controllers\ContentApprovalController::class, 'approve'])->name('approve');
            Route::post('/{approval}/reject', [App\Http\Controllers\ContentApprovalController::class, 'reject'])->name('reject');
            Route::get('/{scheduledPost}/history', [App\Http\Controllers\ContentApprovalController::class, 'getApprovalHistory'])->name('history');
        });
        
        // Dashboard API endpoints
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/kpis', [App\Http\Controllers\DashboardController::class, 'getKPIs'])->name('kpis');
            Route::get('/activity-feed', [App\Http\Controllers\DashboardController::class, 'getActivityFeed'])->name('activity-feed');
            Route::get('/pending-tasks', [App\Http\Controllers\DashboardController::class, 'getPendingTasks'])->name('pending-tasks');
            Route::get('/content-calendar', [App\Http\Controllers\DashboardController::class, 'getContentCalendar'])->name('content-calendar');
            
            // Dashboard Widgets
            Route::prefix('widgets')->name('widgets.')->group(function () {
                Route::get('/', [App\Http\Controllers\DashboardWidgetController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\DashboardWidgetController::class, 'store'])->name('store');
                Route::put('/positions', [App\Http\Controllers\DashboardWidgetController::class, 'updatePositions'])->name('update-positions');
                Route::put('/{widget}', [App\Http\Controllers\DashboardWidgetController::class, 'update'])->name('update');
                Route::delete('/{widget}', [App\Http\Controllers\DashboardWidgetController::class, 'destroy'])->name('destroy');
            });
        });
        
        // Tools Pages (Content Ideation)
        Route::prefix('tools')->name('tools.')->group(function () {
            Route::get('/label-inspiration', [App\Http\Controllers\AI\LabelInspirationController::class, 'index'])->name('label-inspiration');
            Route::get('/product-catalog', [App\Http\Controllers\AI\ProductCatalogController::class, 'index'])->name('product-catalog');
        });
        
        // AI Content Generation
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::prefix('content')->name('content.')->group(function () {
                Route::post('/social-media', [App\Http\Controllers\AI\AiContentController::class, 'generateSocialMediaPost'])->name('social-media');
                Route::post('/press-release', [App\Http\Controllers\AI\AiContentController::class, 'generatePressRelease'])->name('press-release');
                Route::post('/email', [App\Http\Controllers\AI\AiContentController::class, 'generateEmailTemplate'])->name('email');
                Route::post('/blog', [App\Http\Controllers\AI\AiContentController::class, 'generateBlogPost'])->name('blog');
                Route::post('/ad-copy', [App\Http\Controllers\AI\AiContentController::class, 'generateAdCopy'])->name('ad-copy');
                Route::post('/variations', [App\Http\Controllers\AI\AiContentController::class, 'generateVariations'])->name('variations');
            });
            
            // AI Image Generation
            Route::prefix('images')->name('images.')->group(function () {
                Route::post('/generate', [App\Http\Controllers\AI\AiImageController::class, 'generateImage'])->name('generate');
                Route::post('/{image}/optimize', [App\Http\Controllers\AI\AiImageController::class, 'optimizeForPlatform'])->name('optimize');
                Route::post('/{image}/library', [App\Http\Controllers\AI\AiImageController::class, 'addToLibrary'])->name('add-to-library');
            });
            
            // SEO Tools
            Route::prefix('seo')->name('seo.')->group(function () {
                Route::post('/keyword-research', [App\Http\Controllers\AI\SeoController::class, 'researchKeyword'])->name('keyword-research');
                Route::post('/analyze-content', [App\Http\Controllers\AI\SeoController::class, 'analyzeContent'])->name('analyze-content');
                Route::post('/meta-tags', [App\Http\Controllers\AI\SeoController::class, 'generateMetaTags'])->name('meta-tags');
                Route::post('/sitemap', [App\Http\Controllers\AI\SeoController::class, 'generateSitemap'])->name('sitemap');
                Route::post('/competitor', [App\Http\Controllers\AI\SeoController::class, 'analyzeCompetitor'])->name('competitor');
            });
            
            // Label Inspiration
            Route::prefix('label-inspiration')->name('label-inspiration.')->group(function () {
                Route::post('/generate', [App\Http\Controllers\AI\LabelInspirationController::class, 'generateLabels'])->name('generate');
            });
            
            // Product Catalog Generator
            Route::prefix('product-catalog')->name('product-catalog.')->group(function () {
                Route::post('/generate', [App\Http\Controllers\AI\ProductCatalogController::class, 'generateCatalog'])->name('generate');
                Route::post('/descriptions', [App\Http\Controllers\AI\ProductCatalogController::class, 'generateProductDescriptions'])->name('descriptions');
            });
            
            // AI Usage & Quota
            Route::prefix('usage')->name('usage.')->group(function () {
                Route::get('/stats', [App\Http\Controllers\AI\AiUsageController::class, 'getUsageStats'])->name('stats');
                Route::get('/quota', [App\Http\Controllers\AI\AiUsageController::class, 'getRemainingQuota'])->name('quota');
            });
        });
        
        // Email Marketing
        Route::prefix('email-marketing')->name('email-marketing.')->group(function () {
            // Email Campaigns
            Route::prefix('campaigns')->name('campaigns.')->group(function () {
                Route::get('/', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'store'])->name('store');
                Route::get('/{emailCampaign}', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'show'])->name('show');
                Route::put('/{emailCampaign}', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'update'])->name('update');
                Route::delete('/{emailCampaign}', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'destroy'])->name('destroy');
                Route::post('/{emailCampaign}/send', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'send'])->name('send');
                Route::post('/{emailCampaign}/schedule', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'schedule'])->name('schedule');
                Route::post('/{emailCampaign}/pause', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'pause'])->name('pause');
                Route::post('/{emailCampaign}/resume', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'resume'])->name('resume');
                Route::post('/{emailCampaign}/cancel', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'cancel'])->name('cancel');
                Route::get('/{emailCampaign}/metrics', [App\Http\Controllers\EmailMarketing\EmailCampaignController::class, 'getMetrics'])->name('metrics');
            });
            
            // Contacts
            Route::prefix('contacts')->name('contacts.')->group(function () {
                Route::get('/', [App\Http\Controllers\EmailMarketing\ContactController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\EmailMarketing\ContactController::class, 'store'])->name('store');
                Route::post('/import', [App\Http\Controllers\EmailMarketing\ContactController::class, 'import'])->name('import');
                Route::get('/{contact}', [App\Http\Controllers\EmailMarketing\ContactController::class, 'show'])->name('show');
                Route::put('/{contact}', [App\Http\Controllers\EmailMarketing\ContactController::class, 'update'])->name('update');
                Route::delete('/{contact}', [App\Http\Controllers\EmailMarketing\ContactController::class, 'destroy'])->name('destroy');
                Route::get('/{contact}/duplicates', [App\Http\Controllers\EmailMarketing\ContactController::class, 'findDuplicates'])->name('duplicates');
                Route::post('/{contact}/merge', [App\Http\Controllers\EmailMarketing\ContactController::class, 'merge'])->name('merge');
                Route::post('/{contact}/subscribe', [App\Http\Controllers\EmailMarketing\ContactController::class, 'subscribe'])->name('subscribe');
                Route::post('/{contact}/unsubscribe', [App\Http\Controllers\EmailMarketing\ContactController::class, 'unsubscribe'])->name('unsubscribe');
                Route::get('/{contact}/export', [App\Http\Controllers\EmailMarketing\ContactController::class, 'exportData'])->name('export');
                Route::delete('/{contact}/data', [App\Http\Controllers\EmailMarketing\ContactController::class, 'deleteData'])->name('delete-data');
            });
            
            // Email Templates
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [App\Http\Controllers\EmailMarketing\EmailTemplateController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\EmailMarketing\EmailTemplateController::class, 'store'])->name('store');
                Route::get('/{emailTemplate}', [App\Http\Controllers\EmailMarketing\EmailTemplateController::class, 'show'])->name('show');
                Route::put('/{emailTemplate}', [App\Http\Controllers\EmailMarketing\EmailTemplateController::class, 'update'])->name('update');
                Route::delete('/{emailTemplate}', [App\Http\Controllers\EmailMarketing\EmailTemplateController::class, 'destroy'])->name('destroy');
                Route::post('/{emailTemplate}/render', [App\Http\Controllers\EmailMarketing\EmailTemplateController::class, 'render'])->name('render');
            });
            
            // Contact Lists
            Route::prefix('contact-lists')->name('contact-lists.')->group(function () {
                Route::get('/', [App\Http\Controllers\EmailMarketing\ContactListController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\EmailMarketing\ContactListController::class, 'store'])->name('store');
                Route::get('/{contactList}', [App\Http\Controllers\EmailMarketing\ContactListController::class, 'show'])->name('show');
                Route::put('/{contactList}', [App\Http\Controllers\EmailMarketing\ContactListController::class, 'update'])->name('update');
                Route::delete('/{contactList}', [App\Http\Controllers\EmailMarketing\ContactListController::class, 'destroy'])->name('destroy');
                Route::post('/{contactList}/contacts', [App\Http\Controllers\EmailMarketing\ContactListController::class, 'addContacts'])->name('add-contacts');
                Route::delete('/{contactList}/contacts', [App\Http\Controllers\EmailMarketing\ContactListController::class, 'removeContacts'])->name('remove-contacts');
            });
        });

        // Brand Assets Page (brand-scoped)
        Route::middleware('brand.context')->get('/brand-assets', [App\Http\Controllers\Brand\BrandController::class, 'brandAssets'])->name('brand-assets');
        
        // Files Management (brand-scoped)
        Route::middleware('brand.context')->prefix('files')->name('files.')->group(function () {
            Route::get('/', [App\Http\Controllers\FileController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\FileController::class, 'store'])->name('store');
            Route::get('/{file}', [App\Http\Controllers\FileController::class, 'show'])->name('show');
            Route::delete('/{file}', [App\Http\Controllers\FileController::class, 'destroy'])->name('destroy');
            Route::get('/{file}/download', [App\Http\Controllers\FileController::class, 'download'])->name('download');
        });
        
        // Analytics (brand-scoped)
        Route::middleware('brand.context')->prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('index');
            Route::post('/analyze', [App\Http\Controllers\AnalyticsController::class, 'analyze'])->name('analyze');
            Route::get('/reports/{reportId}', [App\Http\Controllers\AnalyticsController::class, 'getAnalysis'])->name('reports.show');
            Route::get('/campaigns/{campaignId}/performance', [App\Http\Controllers\AnalyticsController::class, 'getCampaignPerformance'])->name('campaigns.performance');
            Route::get('/social-media/engagement', [App\Http\Controllers\AnalyticsController::class, 'getSocialMediaEngagement'])->name('social-media.engagement');
            Route::get('/campaigns/{campaignId}/roi', [App\Http\Controllers\AnalyticsController::class, 'calculateROI'])->name('campaigns.roi');
            Route::get('/competitors/comparison', [App\Http\Controllers\AnalyticsController::class, 'getCompetitorComparison'])->name('competitors.comparison');
            Route::post('/sentiment', [App\Http\Controllers\AnalyticsController::class, 'analyzeSentiment'])->name('sentiment.analyze');
            Route::get('/sentiment/trends', [App\Http\Controllers\AnalyticsController::class, 'getSentimentTrends'])->name('sentiment.trends');
            Route::get('/sentiment/alerts', [App\Http\Controllers\AnalyticsController::class, 'getSentimentAlerts'])->name('sentiment.alerts');
            Route::post('/predictions/campaigns/{campaignId}', [App\Http\Controllers\AnalyticsController::class, 'predictCampaignPerformance'])->name('predictions.campaigns');
            Route::post('/predictions/content-engagement', [App\Http\Controllers\AnalyticsController::class, 'predictContentEngagement'])->name('predictions.content-engagement');
            Route::post('/predictions/campaigns/{campaignId}/roi', [App\Http\Controllers\AnalyticsController::class, 'predictROI'])->name('predictions.roi');
            Route::get('/predictions/optimal-posting-times', [App\Http\Controllers\AnalyticsController::class, 'getOptimalPostingTimes'])->name('predictions.optimal-posting-times');
            Route::get('/predictions/budget-optimization', [App\Http\Controllers\AnalyticsController::class, 'getBudgetOptimization'])->name('predictions.budget-optimization');
        });
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\ReportController::class, 'store'])->name('store');
            Route::get('/{reportId}', [App\Http\Controllers\ReportController::class, 'show'])->name('show');
            Route::put('/{reportId}', [App\Http\Controllers\ReportController::class, 'update'])->name('update');
            Route::delete('/{reportId}', [App\Http\Controllers\ReportController::class, 'destroy'])->name('destroy');
            Route::post('/{reportId}/generate', [App\Http\Controllers\ReportController::class, 'generate'])->name('generate');
            Route::post('/{reportId}/schedule', [App\Http\Controllers\ReportController::class, 'schedule'])->name('schedule');
            Route::post('/{reportId}/share', [App\Http\Controllers\ReportController::class, 'share'])->name('share');
            Route::post('/{reportId}/export', [App\Http\Controllers\ReportController::class, 'export'])->name('export');
        });
        
        // Brand Management
        Route::prefix('brands')->name('brands.')->group(function () {
            Route::get('/', [App\Http\Controllers\Brand\BrandController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Brand\BrandController::class, 'store'])->name('store');
            Route::get('/{brand}', [App\Http\Controllers\Brand\BrandController::class, 'show'])->name('show');
            Route::put('/{brand}', [App\Http\Controllers\Brand\BrandController::class, 'update'])->name('update');
            Route::delete('/{brand}', [App\Http\Controllers\Brand\BrandController::class, 'destroy'])->name('destroy');
            
            // Brand Assets
            Route::prefix('{brand}/assets')->name('assets.')->group(function () {
                Route::get('/', [App\Http\Controllers\Brand\BrandAssetController::class, 'index'])->name('index');
                Route::get('/{brandAsset}', [App\Http\Controllers\Brand\BrandAssetController::class, 'show'])->name('show');
                Route::post('/', [App\Http\Controllers\Brand\BrandAssetController::class, 'store'])->name('store');
                Route::put('/{brandAsset}', [App\Http\Controllers\Brand\BrandAssetController::class, 'update'])->name('update');
                Route::delete('/{brandAsset}', [App\Http\Controllers\Brand\BrandAssetController::class, 'destroy'])->name('destroy');
            });
            
            // Brand Name Generator
            Route::prefix('name-generator')->name('name-generator.')->group(function () {
                Route::post('/generate', [App\Http\Controllers\Brand\BrandNameGeneratorController::class, 'generate'])->name('generate');
                Route::post('/check-domain', [App\Http\Controllers\Brand\BrandNameGeneratorController::class, 'checkDomain'])->name('check-domain');
                Route::post('/check-handles', [App\Http\Controllers\Brand\BrandNameGeneratorController::class, 'checkSocialHandles'])->name('check-handles');
            });
        });
        
        // Product Management
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [App\Http\Controllers\Product\ProductController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Product\ProductController::class, 'store'])->name('store');
            Route::post('/import', [App\Http\Controllers\Product\ProductController::class, 'import'])->name('import');
            Route::get('/{product}', [App\Http\Controllers\Product\ProductController::class, 'show'])->name('show');
            Route::put('/{product}', [App\Http\Controllers\Product\ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [App\Http\Controllers\Product\ProductController::class, 'destroy'])->name('destroy');
            Route::put('/{product}/stock', [App\Http\Controllers\Product\ProductController::class, 'updateStock'])->name('update-stock');
            
            // Product Images
            Route::prefix('{product}/images')->name('images.')->group(function () {
                Route::post('/', [App\Http\Controllers\Product\ProductImageController::class, 'store'])->name('store');
                Route::delete('/{productImage}', [App\Http\Controllers\Product\ProductImageController::class, 'destroy'])->name('destroy');
            });
            
            // Product Variants
            Route::prefix('{product}/variants')->name('variants.')->group(function () {
                Route::post('/', [App\Http\Controllers\Product\ProductVariantController::class, 'store'])->name('store');
                Route::put('/{productVariant}', [App\Http\Controllers\Product\ProductVariantController::class, 'update'])->name('update');
                Route::delete('/{productVariant}', [App\Http\Controllers\Product\ProductVariantController::class, 'destroy'])->name('destroy');
            });
        });
        
        // Product Categories
        Route::prefix('product-categories')->name('product-categories.')->group(function () {
            Route::get('/', [App\Http\Controllers\Product\ProductCategoryController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Product\ProductCategoryController::class, 'store'])->name('store');
            Route::put('/{productCategory}', [App\Http\Controllers\Product\ProductCategoryController::class, 'update'])->name('update');
            Route::delete('/{productCategory}', [App\Http\Controllers\Product\ProductCategoryController::class, 'destroy'])->name('destroy');
        });
        
        // Social Media Integration
        Route::prefix('social')->name('social.')->group(function () {
            // OAuth Routes
            Route::prefix('auth')->name('auth.')->group(function () {
                Route::get('/{platform}/redirect', [App\Http\Controllers\SocialMedia\SocialAuthController::class, 'redirectToProvider'])->name('redirect');
                Route::get('/{platform}/callback', [App\Http\Controllers\SocialMedia\SocialAuthController::class, 'handleProviderCallback'])->name('callback');
            });

            // Connections
            Route::prefix('connections')->name('connections.')->group(function () {
                Route::get('/', [App\Http\Controllers\SocialMedia\SocialConnectionController::class, 'index'])->name('index');
                Route::get('/{connection}', [App\Http\Controllers\SocialMedia\SocialConnectionController::class, 'show'])->name('show');
                Route::post('/{connection}/refresh', [App\Http\Controllers\SocialMedia\SocialAuthController::class, 'refresh'])->name('refresh');
                Route::post('/{connection}/check', [App\Http\Controllers\SocialMedia\SocialConnectionController::class, 'checkStatus'])->name('check');
                Route::delete('/{connection}', [App\Http\Controllers\SocialMedia\SocialConnectionController::class, 'destroy'])->name('destroy');
            });

            // Publishing
            Route::prefix('publishing')->name('publishing.')->group(function () {
                Route::post('/{scheduledPost}/publish', [App\Http\Controllers\SocialMedia\PublishingController::class, 'publishNow'])->name('publish');
                Route::post('/{scheduledPost}/publish-multiple', [App\Http\Controllers\SocialMedia\PublishingController::class, 'publishToMultiple'])->name('publish-multiple');
            });

            // Channels
            Route::prefix('channels')->name('channels.')->group(function () {
                Route::get('/', [App\Http\Controllers\ChannelController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\ChannelController::class, 'store'])->name('store');
                Route::get('/{channel}', [App\Http\Controllers\ChannelController::class, 'show'])->name('show');
                Route::put('/{channel}', [App\Http\Controllers\ChannelController::class, 'update'])->name('update');
                Route::delete('/{channel}', [App\Http\Controllers\ChannelController::class, 'destroy'])->name('destroy');
                Route::put('/{channel}/settings', [App\Http\Controllers\ChannelController::class, 'updateSettings'])->name('settings');
            });
        });
        
        // Press Release Management
        Route::prefix('press-releases')->name('press-releases.')->group(function () {
            Route::get('/', [App\Http\Controllers\PressRelease\PressReleaseController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\PressRelease\PressReleaseController::class, 'store'])->name('store');
            Route::get('/{pressRelease}', [App\Http\Controllers\PressRelease\PressReleaseController::class, 'show'])->name('show');
            Route::put('/{pressRelease}', [App\Http\Controllers\PressRelease\PressReleaseController::class, 'update'])->name('update');
            Route::delete('/{pressRelease}', [App\Http\Controllers\PressRelease\PressReleaseController::class, 'destroy'])->name('destroy');
            Route::post('/{pressRelease}/schedule', [App\Http\Controllers\PressRelease\PressReleaseController::class, 'schedule'])->name('schedule');
            Route::post('/{pressRelease}/approve', [App\Http\Controllers\PressRelease\PressReleaseController::class, 'approve'])->name('approve');
            Route::post('/{pressRelease}/distribute', [App\Http\Controllers\PressRelease\PressReleaseController::class, 'distribute'])->name('distribute');
            
            // Press Contacts
            Route::prefix('contacts')->name('contacts.')->group(function () {
                Route::get('/', [App\Http\Controllers\PressRelease\PressContactController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\PressRelease\PressContactController::class, 'store'])->name('store');
                Route::post('/import', [App\Http\Controllers\PressRelease\PressContactController::class, 'import'])->name('import');
                Route::get('/{pressContact}', [App\Http\Controllers\PressRelease\PressContactController::class, 'show'])->name('show');
                Route::put('/{pressContact}', [App\Http\Controllers\PressRelease\PressContactController::class, 'update'])->name('update');
                Route::delete('/{pressContact}', [App\Http\Controllers\PressRelease\PressContactController::class, 'destroy'])->name('destroy');
            });
        });
        
        // Competitor Analysis
        Route::prefix('competitors')->name('competitors.')->group(function () {
            Route::get('/', [App\Http\Controllers\Competitor\CompetitorController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Competitor\CompetitorController::class, 'store'])->name('store');
            Route::get('/{competitor}', [App\Http\Controllers\Competitor\CompetitorController::class, 'show'])->name('show');
            Route::put('/{competitor}', [App\Http\Controllers\Competitor\CompetitorController::class, 'update'])->name('update');
            Route::delete('/{competitor}', [App\Http\Controllers\Competitor\CompetitorController::class, 'destroy'])->name('destroy');
            Route::post('/{competitor}/analysis', [App\Http\Controllers\Competitor\CompetitorController::class, 'createAnalysis'])->name('analysis');
            Route::post('/{competitor}/track-post', [App\Http\Controllers\Competitor\CompetitorController::class, 'trackPost'])->name('track-post');
            Route::post('/compare', [App\Http\Controllers\Competitor\CompetitorController::class, 'compare'])->name('compare');
        });
        
        // Automation & Workflows
        Route::middleware('not.client')->prefix('workflows')->name('workflows.')->group(function () {
            Route::get('/', [App\Http\Controllers\Workflow\WorkflowController::class, 'index'])->name('index');
            Route::get('/builder', [App\Http\Controllers\Workflow\WorkflowController::class, 'builder'])->name('builder');
            Route::post('/', [App\Http\Controllers\Workflow\WorkflowController::class, 'store'])->name('store');
            Route::get('/{workflow}', [App\Http\Controllers\Workflow\WorkflowController::class, 'show'])->name('show');
            Route::put('/{workflow}', [App\Http\Controllers\Workflow\WorkflowController::class, 'update'])->name('update');
            Route::delete('/{workflow}', [App\Http\Controllers\Workflow\WorkflowController::class, 'destroy'])->name('destroy');
            Route::post('/{workflow}/execute', [App\Http\Controllers\Workflow\WorkflowController::class, 'execute'])->name('execute');
        });
        
        // Website Chatbot
        Route::middleware('not.client')->prefix('chatbots')->name('chatbots.')->group(function () {
            Route::get('/', [App\Http\Controllers\Chatbot\ChatbotController::class, 'index'])->name('index');
            Route::get('/builder', [App\Http\Controllers\Chatbot\ChatbotController::class, 'builder'])->name('builder');
            Route::get('/deployment', [App\Http\Controllers\Chatbot\ChatbotController::class, 'deployment'])->name('deployment');
            Route::get('/analytics', [App\Http\Controllers\Chatbot\ChatbotController::class, 'analytics'])->name('analytics');
            Route::post('/', [App\Http\Controllers\Chatbot\ChatbotController::class, 'store'])->name('store');
            Route::get('/{chatbot}', [App\Http\Controllers\Chatbot\ChatbotController::class, 'show'])->name('show');
            Route::put('/{chatbot}', [App\Http\Controllers\Chatbot\ChatbotController::class, 'update'])->name('update');
            Route::delete('/{chatbot}', [App\Http\Controllers\Chatbot\ChatbotController::class, 'destroy'])->name('destroy');
            Route::post('/{chatbot}/conversation', [App\Http\Controllers\Chatbot\ChatbotController::class, 'startConversation'])->name('conversation');
            Route::post('/{chatbot}/lead', [App\Http\Controllers\Chatbot\ChatbotController::class, 'captureLead'])->name('lead');
        });
        
        // Landing Page Builder
        Route::middleware('not.client')->prefix('landing-pages')->name('landing-pages.')->group(function () {
            Route::get('/', [App\Http\Controllers\LandingPage\LandingPageController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\LandingPage\LandingPageController::class, 'store'])->name('store');
            Route::get('/{landingPage}', [App\Http\Controllers\LandingPage\LandingPageController::class, 'show'])->name('show');
            Route::put('/{landingPage}', [App\Http\Controllers\LandingPage\LandingPageController::class, 'update'])->name('update');
            Route::delete('/{landingPage}', [App\Http\Controllers\LandingPage\LandingPageController::class, 'destroy'])->name('destroy');
            Route::post('/{landingPage}/publish', [App\Http\Controllers\LandingPage\LandingPageController::class, 'publish'])->name('publish');
            Route::post('/{landingPage}/variants', [App\Http\Controllers\LandingPage\LandingPageController::class, 'createVariant'])->name('variants.store');
            Route::post('/{landingPage}/set-winner', [App\Http\Controllers\LandingPage\LandingPageController::class, 'setWinner'])->name('set-winner');
        });
        
        // Surveys & Feedback
        Route::prefix('surveys')->name('surveys.')->group(function () {
            Route::get('/', [App\Http\Controllers\Survey\SurveyController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Survey\SurveyController::class, 'store'])->name('store');
            Route::get('/{survey}', [App\Http\Controllers\Survey\SurveyController::class, 'show'])->name('show');
            Route::put('/{survey}', [App\Http\Controllers\Survey\SurveyController::class, 'update'])->name('update');
            Route::delete('/{survey}', [App\Http\Controllers\Survey\SurveyController::class, 'destroy'])->name('destroy');
            Route::post('/{survey}/questions', [App\Http\Controllers\Survey\SurveyController::class, 'addQuestion'])->name('questions.store');
            Route::post('/{survey}/responses', [App\Http\Controllers\Survey\SurveyController::class, 'submitResponse'])->name('responses.store');
            Route::post('/{survey}/activate', [App\Http\Controllers\Survey\SurveyController::class, 'activate'])->name('activate');
            Route::post('/{survey}/close', [App\Http\Controllers\Survey\SurveyController::class, 'close'])->name('close');
        });
        
        // Organization Settings (Admin only)
        Route::middleware('organization.admin')->group(function () {
            // Settings
            Route::get('/settings', [App\Http\Controllers\Organization\SettingsController::class, 'index'])->name('settings');
            Route::put('/settings', [App\Http\Controllers\Organization\SettingsController::class, 'update'])->name('settings.update');
            Route::get('/settings/{key}', [App\Http\Controllers\Organization\SettingsController::class, 'getSetting'])->name('settings.get');
            Route::put('/settings/{key}', [App\Http\Controllers\Organization\SettingsController::class, 'updateSetting'])->name('settings.update-setting');
            Route::delete('/settings/{key}', [App\Http\Controllers\Organization\SettingsController::class, 'deleteSetting'])->name('settings.delete');
            
            // Billing
            Route::get('/billing', [App\Http\Controllers\Organization\BillingController::class, 'index'])->name('billing');
            Route::post('/billing/subscription', [App\Http\Controllers\Organization\BillingController::class, 'createSubscription'])->name('billing.subscription.create');
            Route::put('/billing/subscription/upgrade', [App\Http\Controllers\Organization\BillingController::class, 'upgradeSubscription'])->name('billing.subscription.upgrade');
            Route::put('/billing/subscription/cancel', [App\Http\Controllers\Organization\BillingController::class, 'cancelSubscription'])->name('billing.subscription.cancel');
            Route::get('/billing/invoices', [App\Http\Controllers\Organization\BillingController::class, 'getInvoices'])->name('billing.invoices');
            Route::get('/billing/usage', [App\Http\Controllers\Organization\BillingController::class, 'getUsageStats'])->name('billing.usage');
            
            // Team
            Route::get('/team', [App\Http\Controllers\Organization\TeamController::class, 'index'])->name('team');
            Route::post('/team/members', [App\Http\Controllers\Organization\TeamController::class, 'addMember'])->name('team.members.add');
            Route::delete('/team/members/{userId}', [App\Http\Controllers\Organization\TeamController::class, 'removeMember'])->name('team.members.remove');
            Route::put('/team/members/{userId}/role', [App\Http\Controllers\Organization\TeamController::class, 'updateRole'])->name('team.members.update-role');
            Route::post('/team/invite', [App\Http\Controllers\Organization\TeamController::class, 'invite'])->name('team.invite');
            Route::get('/team/roles', [App\Http\Controllers\Organization\TeamController::class, 'getRoles'])->name('team.roles');
            
            // Storage Sources
            Route::get('/storage-sources', [App\Http\Controllers\Organization\StorageSourceController::class, 'index'])->name('storage-sources');
            Route::post('/storage-sources/connect', [App\Http\Controllers\Organization\StorageSourceController::class, 'connect'])->name('storage-sources.connect');
            Route::delete('/storage-sources/{provider}', [App\Http\Controllers\Organization\StorageSourceController::class, 'disconnect'])->name('storage-sources.disconnect');
            Route::get('/storage-sources/{provider}', [App\Http\Controllers\Organization\StorageSourceController::class, 'show'])->name('storage-sources.show');
            Route::put('/storage-sources/{provider}/settings', [App\Http\Controllers\Organization\StorageSourceController::class, 'updateSettings'])->name('storage-sources.update-settings');
            
            // Automations
            Route::get('/automations', [App\Http\Controllers\Organization\AutomationController::class, 'index'])->name('automations');
        });
    });
});

Route::prefix('admin')->middleware(['auth:admin', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('organizations', App\Http\Controllers\Admin\OrganizationController::class);
    
    // User Management
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/assign-roles', [App\Http\Controllers\Admin\UserController::class, 'assignRoles'])->name('users.assign-roles');
    Route::post('/users/{user}/deactivate', [App\Http\Controllers\Admin\UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/reactivate', [App\Http\Controllers\Admin\UserController::class, 'reactivate'])->name('users.reactivate');
    
    // Content Moderation
    Route::get('/content', [App\Http\Controllers\Admin\ContentController::class, 'index'])->name('content.index');
    Route::post('/content/flag/{post}', [App\Http\Controllers\Admin\ContentController::class, 'flag'])->name('content.flag');
    Route::post('/content/{moderation}/approve', [App\Http\Controllers\Admin\ContentController::class, 'approve'])->name('content.approve');
    Route::post('/content/{moderation}/reject', [App\Http\Controllers\Admin\ContentController::class, 'reject'])->name('content.reject');
    Route::delete('/content/{moderation}', [App\Http\Controllers\Admin\ContentController::class, 'destroy'])->name('content.destroy');
    Route::post('/content/flags/{flag}/review', [App\Http\Controllers\Admin\ContentController::class, 'reviewFlag'])->name('content.review-flag');
    
    // Platform Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/update', [App\Http\Controllers\Admin\SettingsController::class, 'updateSetting'])->name('settings.update');
    Route::post('/settings/feature-flags/{featureFlag}/toggle', [App\Http\Controllers\Admin\SettingsController::class, 'toggleFeatureFlag'])->name('settings.toggle-feature-flag');
    Route::post('/settings/feature-flags', [App\Http\Controllers\Admin\SettingsController::class, 'updateFeatureFlag'])->name('settings.update-feature-flag');
    Route::post('/settings/api-keys', [App\Http\Controllers\Admin\SettingsController::class, 'updateApiKey'])->name('settings.update-api-key');
    Route::post('/settings/maintenance/enable', [App\Http\Controllers\Admin\SettingsController::class, 'enableMaintenanceMode'])->name('settings.enable-maintenance');
    Route::post('/settings/maintenance/disable', [App\Http\Controllers\Admin\SettingsController::class, 'disableMaintenanceMode'])->name('settings.disable-maintenance');
    
    // System Logs
    Route::get('/logs', [App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
    
    // Admin Analytics & Costing
    Route::get('/costing', [App\Http\Controllers\Admin\CostingController::class, 'index'])->name('costing.index');
    
    // Other admin routes (to be implemented)
    Route::resource('packages', App\Http\Controllers\Admin\PackageController::class);
    Route::get('/billing', [App\Http\Controllers\Admin\BillingController::class, 'index'])->name('billing.index');
    Route::get('/team', [App\Http\Controllers\Admin\TeamController::class, 'index'])->name('team.index');
});

Route::middleware(['auth', 'verified', 'role:agency'])->prefix('agency')->name('agency.')->group(function () {
    Route::middleware('agency')->prefix('{agency}')->group(function () {
        Route::get('/', [App\Http\Controllers\Agency\DashboardController::class, 'index'])->name('dashboard');
        
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [App\Http\Controllers\Agency\ClientController::class, 'index'])->name('index');
            Route::middleware('agency.client')->get('/{organizationId}', [App\Http\Controllers\Agency\ClientController::class, 'show'])->name('show');
        });
        
        Route::get('/tasks', [App\Http\Controllers\Agency\TaskController::class, 'index'])->name('tasks');
        Route::get('/calendar', [App\Http\Controllers\Agency\CalendarController::class, 'index'])->name('calendar');
        
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\Agency\ReportController::class, 'index'])->name('index');
            Route::post('/generate', [App\Http\Controllers\Agency\ReportController::class, 'generate'])->name('generate');
            Route::get('/{report}/download', [App\Http\Controllers\Agency\ReportController::class, 'download'])->name('download');
        });
        
        // Admin-only routes
        Route::middleware('agency.admin')->group(function () {
            Route::prefix('billing')->name('billing.')->group(function () {
                Route::get('/', [App\Http\Controllers\Agency\BillingController::class, 'index'])->name('index');
                Route::post('/{invoice}/pay', [App\Http\Controllers\Agency\BillingController::class, 'pay'])->name('pay');
                Route::get('/{invoice}/download', [App\Http\Controllers\Agency\BillingController::class, 'download'])->name('download');
                Route::post('/send-reminders', [App\Http\Controllers\Agency\BillingController::class, 'sendReminders'])->name('send-reminders');
            });
            
            Route::prefix('team')->name('team.')->group(function () {
                Route::get('/', [App\Http\Controllers\Agency\TeamController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\Agency\TeamController::class, 'store'])->name('store');
                Route::put('/{user}', [App\Http\Controllers\Agency\TeamController::class, 'update'])->name('update');
                Route::delete('/{user}', [App\Http\Controllers\Agency\TeamController::class, 'destroy'])->name('destroy');
            });
            
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [App\Http\Controllers\Agency\SettingsController::class, 'index'])->name('index');
                Route::put('/profile', [App\Http\Controllers\Agency\SettingsController::class, 'updateProfile'])->name('update-profile');
                Route::put('/branding', [App\Http\Controllers\Agency\SettingsController::class, 'updateBranding'])->name('update-branding');
                Route::put('/defaults', [App\Http\Controllers\Agency\SettingsController::class, 'updateDefaults'])->name('update-defaults');
                Route::put('/integrations', [App\Http\Controllers\Agency\SettingsController::class, 'updateIntegrations'])->name('update-integrations');
                Route::put('/notifications', [App\Http\Controllers\Agency\SettingsController::class, 'updateNotifications'])->name('update-notifications');
            });
        });
    });
});

// Webhook routes (no auth required)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/facebook', [App\Http\Controllers\SocialMedia\WebhookController::class, 'handleFacebook'])->name('facebook');
    Route::post('/instagram', [App\Http\Controllers\SocialMedia\WebhookController::class, 'handleInstagram'])->name('instagram');
    Route::post('/linkedin', [App\Http\Controllers\SocialMedia\WebhookController::class, 'handleLinkedIn'])->name('linkedin');
    Route::post('/twitter', [App\Http\Controllers\SocialMedia\WebhookController::class, 'handleTwitter'])->name('twitter');
    Route::post('/tiktok', [App\Http\Controllers\SocialMedia\WebhookController::class, 'handleTikTok'])->name('tiktok');
    Route::post('/pinterest', [App\Http\Controllers\SocialMedia\WebhookController::class, 'handlePinterest'])->name('pinterest');
});

// Email tracking routes (no auth required)
Route::prefix('email')->name('email.')->group(function () {
    Route::get('/track/open/{token}', [App\Http\Controllers\EmailMarketing\EmailTrackingController::class, 'trackOpen'])->name('track.open');
    Route::get('/track/click/{token}', [App\Http\Controllers\EmailMarketing\EmailTrackingController::class, 'trackClick'])->name('track.click');
    Route::get('/unsubscribe/{token}', [App\Http\Controllers\EmailMarketing\EmailTrackingController::class, 'unsubscribe'])->name('unsubscribe');
});


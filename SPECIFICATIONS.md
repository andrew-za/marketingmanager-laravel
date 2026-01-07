# MarketPulse - Laravel 12 Redevelopment Specifications

## Table of Contents
1. [Overview](#overview)
2. [Architecture & Technology Stack](#architecture--technology-stack)
3. [Laravel Development Guidelines](#laravel-development-guidelines)
4. [Core Modules](#core-modules)
5. [Database Schema](#database-schema)
6. [API Specifications](#api-specifications)
7. [Security Requirements](#security-requirements)
8. [Performance Requirements](#performance-requirements)
9. [Integration Requirements](#integration-requirements)
10. [Deployment Specifications](#deployment-specifications)
11. [Migration Strategy](#migration-strategy)

---

## Overview

MarketPulse is a comprehensive, AI-powered marketing automation platform designed to streamline digital marketing campaigns, enhance team collaboration, and deliver actionable insights. This document outlines the complete feature specifications for redeveloping the application using Laravel 12.

### Key Objectives
- Migrate from Next.js/React to Laravel 12 with modern frontend (Livewire 3 + Alpine.js or Inertia.js)
- Implement robust multi-tenancy architecture
- Build scalable AI integration layer
- Ensure enterprise-grade security and performance
- Maintain backward compatibility during migration

### Target Users
- **Marketing Teams**: Content creators, social media managers, campaign strategists
- **Agencies**: Multi-client management, white-label reporting, team collaboration
- **Administrators**: Platform management, user oversight, system configuration
- **End Clients**: Brand owners, product managers, business stakeholders

---

## Architecture & Technology Stack

### Backend Framework
- **Laravel 12**: Latest stable version with PHP 8.3+
- **Database**: MySQL 8.0+ / PostgreSQL 15+ (configurable)
- **Cache**: Redis 7.0+ for sessions, queues, and caching
- **Queue System**: Laravel Queues with Redis driver
- **File Storage**: Laravel Filesystem (S3, local, or configurable)

### Frontend Stack
- **Option A**: Livewire 3 + Alpine.js (recommended for rapid development)
- **Option B**: Inertia.js + Vue 3 / React (for existing React components)
- **UI Framework**: Tailwind CSS 3.4+
- **Icons**: Lucide Icons / Heroicons
- **Charts**: Chart.js / Recharts integration

### AI & External Services
- **AI Provider**: Google Gemini AI (Genkit) / OpenAI (configurable)
- **Image Generation**: DALL-E / Midjourney API / Stable Diffusion
- **Email Service**: Laravel Mail with SendGrid / Mailgun / SES
- **Payment Gateway**: Stripe / PayPal integration
- **Social Media APIs**: Facebook Graph API, Instagram Basic Display, LinkedIn API, Twitter API v2, TikTok API

### Development Tools
- **Testing**: PHPUnit, Pest PHP
- **Code Quality**: PHPStan, Laravel Pint
- **API Documentation**: Laravel API Documentation (Scribe/Scramble)
- **Monitoring**: Laravel Telescope, Sentry
- **Task Scheduling**: Laravel Scheduler (Cron)

---

## Laravel Development Guidelines

This section provides comprehensive guidelines for Laravel developers to ensure the codebase follows Object-Oriented Programming (OOP) principles, DRY (Don't Repeat Yourself) methodology, and maintains a well-structured, maintainable architecture.

### Project Structure & Organization

#### Directory Structure
```
app/
├── Console/
│   └── Commands/          # Custom Artisan commands
├── Events/                # Event classes
├── Exceptions/            # Custom exception handlers
├── Http/
│   ├── Controllers/       # Organized by feature/module
│   │   ├── Admin/
│   │   ├── Agency/
│   │   ├── Campaign/
│   │   ├── Content/
│   │   └── ...
│   ├── Middleware/        # Custom middleware
│   ├── Requests/          # Form request validation
│   │   ├── Campaign/
│   │   ├── Content/
│   │   └── ...
│   └── Resources/         # API resources
│       ├── Campaign/
│       └── ...
├── Models/                # Eloquent models
│   ├── Campaign.php
│   ├── Organization.php
│   └── ...
├── Policies/              # Authorization policies
├── Providers/             # Service providers
├── Services/              # Business logic services
│   ├── Campaign/
│   │   ├── CampaignService.php
│   │   ├── CampaignSchedulingService.php
│   │   └── CampaignAnalyticsService.php
│   ├── AI/
│   │   ├── ContentGenerationService.php
│   │   └── ImageGenerationService.php
│   └── ...
├── Repositories/          # Data access layer (optional)
│   ├── Contracts/         # Repository interfaces
│   └── Eloquent/          # Eloquent implementations
├── Jobs/                  # Queue jobs
├── Listeners/             # Event listeners
├── Notifications/         # Notification classes
├── Rules/                 # Custom validation rules
└── Support/              # Helper classes
    ├── Traits/
    └── Helpers/
```

#### Module-Based Organization
Organize code by feature/module rather than by technical layer:
- Each module (Campaign, Content, Brand, etc.) contains its own controllers, services, models, requests, resources
- Promotes cohesion and reduces coupling
- Easier to locate related code

### Object-Oriented Programming Principles

#### SOLID Principles

**1. Single Responsibility Principle (SRP)**
- Each class should have one reason to change
- Separate concerns: Controllers handle HTTP, Services handle business logic, Models handle data

```php
// ❌ BAD: Controller doing too much
class CampaignController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([...]);
        
        // Business logic
        $campaign = new Campaign();
        $campaign->name = $validated['name'];
        // ... complex logic
        
        // External API call
        $this->publishToSocialMedia($campaign);
        
        // Email notification
        Mail::to($user)->send(new CampaignCreated($campaign));
        
        return response()->json($campaign);
    }
}

// ✅ GOOD: Separated responsibilities
class CampaignController extends Controller
{
    public function __construct(
        private CampaignService $campaignService
    ) {}
    
    public function store(CreateCampaignRequest $request)
    {
        $campaign = $this->campaignService->createCampaign(
            $request->validated(),
            $request->user()
        );
        
        return new CampaignResource($campaign);
    }
}

class CampaignService
{
    public function __construct(
        private CampaignRepository $repository,
        private CampaignNotificationService $notificationService,
        private SocialMediaService $socialMediaService
    ) {}
    
    public function createCampaign(array $data, User $user): Campaign
    {
        $campaign = $this->repository->create($data, $user);
        
        $this->notificationService->notifyCampaignCreated($campaign);
        
        return $campaign;
    }
}
```

**2. Open/Closed Principle (OCP)**
- Open for extension, closed for modification
- Use interfaces and abstract classes for extensibility

```php
// ✅ GOOD: Interface-based design
interface ContentGeneratorInterface
{
    public function generate(array $parameters): string;
}

class SocialMediaPostGenerator implements ContentGeneratorInterface
{
    public function generate(array $parameters): string
    {
        // Implementation
    }
}

class PressReleaseGenerator implements ContentGeneratorInterface
{
    public function generate(array $parameters): string
    {
        // Implementation
    }
}

class ContentGenerationService
{
    public function __construct(
        private ContentGeneratorInterface $generator
    ) {}
    
    public function generateContent(array $parameters): string
    {
        return $this->generator->generate($parameters);
    }
}
```

**3. Liskov Substitution Principle (LSP)**
- Derived classes must be substitutable for their base classes
- Ensure inheritance hierarchies are properly designed

**4. Interface Segregation Principle (ISP)**
- Clients should not depend on interfaces they don't use
- Create specific, focused interfaces

```php
// ❌ BAD: Fat interface
interface ContentServiceInterface
{
    public function create();
    public function update();
    public function delete();
    public function publish();
    public function schedule();
    public function analyze();
    public function generateReport();
}

// ✅ GOOD: Segregated interfaces
interface ContentRepositoryInterface
{
    public function create(array $data): Content;
    public function update(Content $content, array $data): Content;
    public function delete(Content $content): bool;
}

interface ContentPublishingInterface
{
    public function publish(Content $content): void;
    public function schedule(Content $content, Carbon $date): void;
}

interface ContentAnalyticsInterface
{
    public function analyze(Content $content): array;
    public function generateReport(Content $content): Report;
}
```

**5. Dependency Inversion Principle (DIP)**
- Depend on abstractions, not concretions
- Use dependency injection throughout

```php
// ✅ GOOD: Dependency injection
class CampaignService
{
    public function __construct(
        private CampaignRepositoryInterface $repository,
        private AIServiceInterface $aiService,
        private NotificationServiceInterface $notificationService
    ) {}
    
    // Methods use injected dependencies
}
```

### DRY (Don't Repeat Yourself) Principles

#### 1. Extract Common Logic to Services
```php
// ❌ BAD: Repeated logic in controllers
class CampaignController extends Controller
{
    public function store(Request $request)
    {
        $campaign = Campaign::create($request->all());
        
        // Repeated notification logic
        foreach ($campaign->teamMembers as $member) {
            Notification::send($member, new CampaignCreated($campaign));
        }
    }
    
    public function update(Request $request, Campaign $campaign)
    {
        $campaign->update($request->all());
        
        // Same notification logic repeated
        foreach ($campaign->teamMembers as $member) {
            Notification::send($member, new CampaignUpdated($campaign));
        }
    }
}

// ✅ GOOD: Extracted to service
class CampaignNotificationService
{
    public function notifyTeamMembers(Campaign $campaign, string $event): void
    {
        $notification = match($event) {
            'created' => new CampaignCreated($campaign),
            'updated' => new CampaignUpdated($campaign),
            'published' => new CampaignPublished($campaign),
        };
        
        $campaign->teamMembers->each(function ($member) use ($notification) {
            Notification::send($member, $notification);
        });
    }
}
```

#### 2. Use Traits for Shared Functionality
```php
// ✅ GOOD: Reusable trait
trait HasOrganizationScope
{
    public static function bootHasOrganizationScope(): void
    {
        static::addGlobalScope(new OrganizationScope);
    }
    
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

// Use in multiple models
class Campaign extends Model
{
    use HasOrganizationScope;
}

class Brand extends Model
{
    use HasOrganizationScope;
}
```

#### 3. Create Reusable Form Requests
```php
// ✅ GOOD: Base form request with common validation
abstract class BaseFormRequest extends FormRequest
{
    protected function commonRules(): array
    {
        return [
            'organization_id' => ['required', 'exists:organizations,id'],
        ];
    }
}

class CreateCampaignRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge($this->commonRules(), [
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date', 'after:today'],
            // ...
        ]);
    }
}
```

#### 4. Use Service Classes for Business Logic
```php
// ✅ GOOD: Centralized business logic
class CampaignSchedulingService
{
    public function __construct(
        private ScheduledPostRepository $postRepository,
        private TimezoneService $timezoneService
    ) {}
    
    public function schedulePost(
        Campaign $campaign,
        array $channels,
        Carbon $scheduledAt
    ): Collection {
        return collect($channels)->map(function ($channel) use ($campaign, $scheduledAt) {
            return $this->postRepository->create([
                'campaign_id' => $campaign->id,
                'channel_id' => $channel['id'],
                'content' => $channel['content'],
                'scheduled_at' => $this->timezoneService->convertToUTC(
                    $scheduledAt,
                    $campaign->organization->timezone
                ),
            ]);
        });
    }
}
```

### Code Organization Patterns

#### 1. Service Layer Pattern
- Controllers are thin, delegate to services
- Services contain business logic
- Services can call other services
- Services return domain objects, not HTTP responses

```php
// ✅ GOOD: Service layer pattern
class CampaignService
{
    public function __construct(
        private CampaignRepository $repository,
        private CampaignValidationService $validationService,
        private CampaignNotificationService $notificationService
    ) {}
    
    public function createCampaign(array $data, User $user): Campaign
    {
        $this->validationService->validateCampaignCreation($data, $user);
        
        $campaign = $this->repository->create([
            ...$data,
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
        ]);
        
        $this->notificationService->notifyCampaignCreated($campaign);
        
        return $campaign;
    }
}
```

#### 2. Repository Pattern (Optional)
- Abstract data access logic
- Makes testing easier
- Allows switching data sources

```php
// ✅ GOOD: Repository pattern
interface CampaignRepositoryInterface
{
    public function find(int $id): ?Campaign;
    public function create(array $data): Campaign;
    public function update(Campaign $campaign, array $data): Campaign;
    public function delete(Campaign $campaign): bool;
    public function findByOrganization(int $organizationId): Collection;
}

class EloquentCampaignRepository implements CampaignRepositoryInterface
{
    public function find(int $id): ?Campaign
    {
        return Campaign::find($id);
    }
    
    public function create(array $data): Campaign
    {
        return Campaign::create($data);
    }
    
    // ... other methods
}
```

#### 3. Action Classes for Complex Operations
- Single-purpose classes for complex operations
- Easier to test and maintain

```php
// ✅ GOOD: Action class for complex operation
class PublishCampaignAction
{
    public function __construct(
        private CampaignRepository $campaignRepository,
        private SocialMediaService $socialMediaService,
        private CampaignAnalyticsService $analyticsService
    ) {}
    
    public function execute(Campaign $campaign): void
    {
        DB::transaction(function () use ($campaign) {
            $this->validateCampaign($campaign);
            $this->publishToChannels($campaign);
            $this->updateCampaignStatus($campaign);
            $this->trackAnalytics($campaign);
        });
    }
    
    private function validateCampaign(Campaign $campaign): void
    {
        if (!$campaign->isReadyToPublish()) {
            throw new CampaignNotReadyException();
        }
    }
    
    // ... other private methods
}
```

### Laravel-Specific Best Practices

#### 1. Use Form Requests for Validation
```php
// ✅ GOOD: Form request validation
class CreateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Campaign::class);
    }
    
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date', 'after:today'],
            'budget' => ['required', 'numeric', 'min:0'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*.id' => ['required', 'exists:channels,id'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Campaign name is required.',
            'start_date.after' => 'Start date must be in the future.',
        ];
    }
}
```

#### 2. Use API Resources for Response Transformation
```php
// ✅ GOOD: API resource for consistent responses
class CampaignResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'start_date' => $this->start_date->toIso8601String(),
            'budget' => [
                'allocated' => $this->budget,
                'spent' => $this->spent,
                'remaining' => $this->budget - $this->spent,
            ],
            'channels' => ChannelResource::collection($this->channels),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

#### 3. Use Policies for Authorization
```php
// ✅ GOOD: Policy-based authorization
class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('campaigns.view');
    }
    
    public function view(User $user, Campaign $campaign): bool
    {
        return $user->organization_id === $campaign->organization_id
            && $user->hasPermission('campaigns.view');
    }
    
    public function create(User $user): bool
    {
        return $user->hasPermission('campaigns.create');
    }
    
    public function update(User $user, Campaign $campaign): bool
    {
        return $user->organization_id === $campaign->organization_id
            && $user->hasPermission('campaigns.update');
    }
    
    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->organization_id === $campaign->organization_id
            && $user->hasPermission('campaigns.delete');
    }
}
```

#### 4. Use Events and Listeners for Decoupled Logic
```php
// ✅ GOOD: Event-driven architecture
class CampaignCreated extends Event
{
    public function __construct(
        public Campaign $campaign
    ) {}
}

class SendCampaignCreatedNotifications implements ShouldQueue
{
    public function handle(CampaignCreated $event): void
    {
        $event->campaign->teamMembers->each(function ($member) use ($event) {
            $member->notify(new CampaignCreatedNotification($event->campaign));
        });
    }
}

class TrackCampaignCreationAnalytics implements ShouldQueue
{
    public function handle(CampaignCreated $event): void
    {
        Analytics::track('campaign.created', [
            'campaign_id' => $event->campaign->id,
            'organization_id' => $event->campaign->organization_id,
        ]);
    }
}
```

#### 5. Use Jobs for Async Processing
```php
// ✅ GOOD: Queue jobs for heavy operations
class PublishScheduledPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public ScheduledPost $post
    ) {}
    
    public function handle(SocialMediaService $socialMediaService): void
    {
        try {
            $socialMediaService->publish($this->post);
            $this->post->markAsPublished();
        } catch (\Exception $e) {
            $this->post->markAsFailed($e->getMessage());
            throw $e;
        }
    }
}
```

### Naming Conventions

#### Classes
- **Controllers**: `CampaignController`, `ContentManagementController`
- **Services**: `CampaignService`, `ContentGenerationService`
- **Repositories**: `CampaignRepository`, `EloquentCampaignRepository`
- **Actions**: `CreateCampaignAction`, `PublishCampaignAction`
- **Jobs**: `PublishScheduledPost`, `SendEmailCampaign`
- **Events**: `CampaignCreated`, `PostPublished`
- **Listeners**: `SendCampaignCreatedNotifications`
- **Policies**: `CampaignPolicy`, `ContentPolicy`
- **Form Requests**: `CreateCampaignRequest`, `UpdateCampaignRequest`
- **Resources**: `CampaignResource`, `CampaignCollection`

#### Methods
- **Controllers**: `index`, `store`, `show`, `update`, `destroy`
- **Services**: Use descriptive verbs: `createCampaign`, `publishCampaign`, `schedulePost`
- **Repositories**: `find`, `create`, `update`, `delete`, `findByOrganization`

#### Variables
- Use descriptive names: `$campaign`, `$scheduledPost`, `$organization`
- Avoid abbreviations: `$camp` ❌, `$campaign` ✅
- Boolean variables: `$isPublished`, `$hasPermission`, `$canEdit`

### Code Quality Standards

#### 1. Method Length
- Keep methods under 20 lines when possible
- Extract complex logic to private methods
- Use early returns to reduce nesting

```php
// ✅ GOOD: Short, focused methods
public function createCampaign(array $data, User $user): Campaign
{
    $this->validateCampaignData($data, $user);
    
    $campaign = $this->repository->create($this->prepareCampaignData($data, $user));
    
    $this->handleCampaignCreated($campaign);
    
    return $campaign;
}

private function validateCampaignData(array $data, User $user): void
{
    // Validation logic
}

private function prepareCampaignData(array $data, User $user): array
{
    // Data preparation logic
}

private function handleCampaignCreated(Campaign $campaign): void
{
    // Post-creation logic
}
```

#### 2. Class Size
- Keep classes focused and under 300 lines
- Split large classes into smaller, focused classes
- Use composition over inheritance

#### 3. Comments and Documentation
```php
/**
 * Service for managing campaign operations.
 * 
 * Handles campaign creation, updates, publishing, and scheduling.
 * Delegates to specialized services for complex operations.
 */
class CampaignService
{
    /**
     * Create a new campaign.
     * 
     * Validates the campaign data, creates the campaign record,
     * and sends notifications to team members.
     * 
     * @param array $data Campaign data
     * @param User $user User creating the campaign
     * @return Campaign Created campaign instance
     * @throws ValidationException If campaign data is invalid
     * @throws AuthorizationException If user lacks permission
     */
    public function createCampaign(array $data, User $user): Campaign
    {
        // Implementation
    }
}
```

### Testing Structure

#### Test Organization
```
tests/
├── Feature/
│   ├── Campaign/
│   │   ├── CampaignCreationTest.php
│   │   ├── CampaignPublishingTest.php
│   │   └── CampaignSchedulingTest.php
│   └── Content/
│       └── ...
├── Unit/
│   ├── Services/
│   │   ├── CampaignServiceTest.php
│   │   └── ...
│   └── Models/
│       └── ...
└── TestCase.php
```

#### Testing Best Practices
```php
// ✅ GOOD: Feature test
class CampaignCreationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_create_campaign(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization);
        
        $response = $this->actingAs($user)
            ->postJson('/api/campaigns', [
                'name' => 'Test Campaign',
                'start_date' => now()->addDay(),
                'budget' => 1000,
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'status',
                ],
            ]);
        
        $this->assertDatabaseHas('campaigns', [
            'name' => 'Test Campaign',
            'organization_id' => $organization->id,
        ]);
    }
}

// ✅ GOOD: Unit test
class CampaignServiceTest extends TestCase
{
    public function test_create_campaign_validates_data(): void
    {
        $service = new CampaignService(
            Mockery::mock(CampaignRepository::class),
            Mockery::mock(CampaignValidationService::class),
            Mockery::mock(CampaignNotificationService::class)
        );
        
        // Test implementation
    }
}
```

### Multi-Tenancy Implementation

#### Global Scopes
```php
// ✅ GOOD: Organization scope for multi-tenancy
class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($organizationId = $this->getOrganizationId()) {
            $builder->where('organization_id', $organizationId);
        }
    }
    
    protected function getOrganizationId(): ?int
    {
        return auth()->user()?->organization_id;
    }
}

class Campaign extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new OrganizationScope);
    }
}
```

#### Middleware for Tenant Context
```php
// ✅ GOOD: Tenant middleware
class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            app()->instance('organization_id', $user->organization_id);
        }
        
        return $next($request);
    }
}
```

### Error Handling

#### Custom Exceptions
```php
// ✅ GOOD: Custom exceptions
class CampaignNotReadyException extends Exception
{
    public function __construct(Campaign $campaign)
    {
        parent::__construct(
            "Campaign '{$campaign->name}' is not ready to be published."
        );
    }
}

class InsufficientBudgetException extends Exception
{
    public function __construct(float $required, float $available)
    {
        parent::__construct(
            "Insufficient budget. Required: {$required}, Available: {$available}"
        );
    }
}
```

#### Exception Handling in Services
```php
// ✅ GOOD: Proper exception handling
class CampaignService
{
    public function publishCampaign(Campaign $campaign): void
    {
        try {
            $this->validateCampaign($campaign);
            $this->publishToChannels($campaign);
            $campaign->markAsPublished();
        } catch (CampaignNotReadyException $e) {
            Log::warning('Campaign not ready', ['campaign_id' => $campaign->id]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to publish campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);
            throw new CampaignPublishingException('Failed to publish campaign', 0, $e);
        }
    }
}
```

### Performance Optimization

#### Eager Loading
```php
// ✅ GOOD: Eager loading relationships
class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with(['channels', 'organization', 'createdBy'])
            ->where('organization_id', auth()->user()->organization_id)
            ->paginate();
        
        return CampaignResource::collection($campaigns);
    }
}
```

#### Query Optimization
```php
// ✅ GOOD: Optimized queries
class CampaignRepository
{
    public function findWithRelations(int $id): ?Campaign
    {
        return Campaign::with([
            'channels:id,name,type',
            'organization:id,name',
            'scheduledPosts' => function ($query) {
                $query->select('id', 'campaign_id', 'scheduled_at', 'status')
                    ->where('scheduled_at', '>', now());
            },
        ])->find($id);
    }
}
```

### Summary Checklist

When writing code, ensure:
- ✅ Single Responsibility: Each class has one reason to change
- ✅ Dependency Injection: Depend on abstractions, inject dependencies
- ✅ DRY: Extract common logic to services, traits, or base classes
- ✅ Thin Controllers: Controllers delegate to services
- ✅ Form Requests: Use for validation and authorization
- ✅ API Resources: Use for response transformation
- ✅ Policies: Use for authorization logic
- ✅ Events/Listeners: Use for decoupled side effects
- ✅ Jobs: Use for async processing
- ✅ Proper Naming: Descriptive, consistent naming conventions
- ✅ Documentation: PHPDoc comments for public methods
- ✅ Testing: Write tests for critical functionality
- ✅ Multi-Tenancy: Always scope queries by organization
- ✅ Error Handling: Proper exception handling and logging

---

## Core Modules

### 1. Authentication & Authorization

#### 1.1 Authentication Flows & User Types

**Three Distinct User Types:**

1. **Customer/Organization Users** - Regular users managing their own organization
2. **Agency Users** - Users managing multiple client organizations
3. **Admin Users** - Platform administrators with system-wide access

**Authentication Routes:**

**Public Routes:**
- `GET /` - Landing page
- `GET /login` - Customer/Organization login page
- `POST /login` - Customer/Organization login handler
- `GET /signup` - Customer/Organization registration page
- `POST /signup` - Customer/Organization registration handler
- `GET /admin/login` - Admin login page (separate from customer login)
- `POST /admin/login` - Admin login handler
- `GET /password/reset` - Password reset request
- `POST /password/reset` - Password reset handler
- `GET /email/verify` - Email verification

**Laravel Implementation:**
```php
// routes/web.php
Route::get('/', [LandingPageController::class, 'index'])->name('home');

// Customer/Organization Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [Auth\LoginController::class, 'login']);
    Route::get('/signup', [Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/signup', [Auth\RegisterController::class, 'register']);
});

// Admin Authentication (Separate)
Route::prefix('admin')->middleware('guest:admin')->group(function () {
    Route::get('/login', [Admin\Auth\LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [Admin\Auth\LoginController::class, 'login']);
});

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Customer/Organization routes
    Route::prefix('main')->group(function () {
        Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
        // ... other customer routes
    });
});

// Admin Routes
Route::prefix('admin')->middleware(['auth:admin', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    // ... other admin routes
});

// Agency Routes
Route::prefix('agency')->middleware(['auth', 'role:agency'])->group(function () {
    Route::get('/{agencyId}', [Agency\DashboardController::class, 'index'])->name('agency.dashboard');
    // ... other agency routes
});
```

#### 1.2 User Management
**Features:**
- User registration with email verification
- Multi-factor authentication (MFA) support
- Password reset functionality
- Profile management (avatar, preferences, timezone)
- Account deactivation/reactivation
- Session management across devices
- Role-based routing (customer, agency, admin)

**Database Tables:**
- `users` (id, name, email, email_verified_at, password, avatar, timezone, status, user_type, created_at, updated_at)
- `user_sessions` (id, user_id, ip_address, user_agent, last_activity, created_at)
- `password_reset_tokens` (email, token, created_at)
- `email_verification_tokens` (email, token, created_at)

**User Type Field:**
- `user_type` enum: 'customer', 'agency', 'admin'
- Determines default redirect after login
- Controls access to different sections

**Laravel Implementation:**
- Laravel Sanctum for API authentication
- Laravel Breeze/Fortify for authentication scaffolding
- Custom middleware for MFA verification
- Policies for authorization checks
- Custom login redirects based on user type

#### 1.3 Role-Based Access Control (RBAC)
**Features:**
- Hierarchical role system (Super Admin, Admin, Editor, Viewer, Agency Admin, Agency Member)
- Permission-based access control
- Organization-level role assignments
- Agency-level role assignments
- Custom role creation for organizations

**Database Tables:**
- `roles` (id, name, slug, description, level, created_at, updated_at)
- `permissions` (id, name, slug, description, module, created_at, updated_at)
- `role_permissions` (role_id, permission_id)
- `user_roles` (id, user_id, role_id, organization_id, agency_id, created_at, updated_at)

**Laravel Implementation:**
- Spatie Laravel Permission package or custom implementation
- Policy classes for each resource
- Middleware for role/permission checks
- Blade components for UI permission checks

---

#### 1.4 Application Sitemaps & Route Structures

**Three Distinct User Portals:**

1. **Customer/Organization Portal** - `/main/[organizationId]/*`
2. **Agency Portal** - `/agency/[agencyId]/*`
3. **Admin Portal** - `/admin/*`

#### Customer/Organization Application Sitemap

**Public Routes:**
- `/` - Landing page
- `/login` - Customer login
- `/signup` - Customer registration
- `/password/reset` - Password reset

**Pre-Organization Routes:**
- `/main/organizations` - Organization selection/creation
- `/main/onboarding` - New user onboarding wizard

**Main Application Routes (Organization Context):**

**Dashboard & Home:**
- `/main/[organizationId]` - Main dashboard
- `/main/[organizationId]/collaboration` - Collaboration hub (home)

**Campaigns:**
- `/main/[organizationId]/campaigns` - Campaign list
- `/main/[organizationId]/campaigns/create` - Create campaign
- `/main/[organizationId]/campaigns/content` - Content management
- `/main/[organizationId]/campaigns/competitions` - Social media competitions

**Content & Publishing:**
- `/main/[organizationId]/channels` - Channel management
- `/main/[organizationId]/email` - Email campaigns
- `/main/[organizationId]/landing-pages` - Landing page builder
- `/main/[organizationId]/landing-pages/[pageId]` - Edit landing page

**Brands & Products:**
- `/main/[organizationId]/brands` - Brand management
- `/main/[organizationId]/brands/choose-name` - Brand name generator
- `/main/[organizationId]/brands/ideation` - Brand ideation
- `/main/[organizationId]/brand-assets` - Brand asset library
- `/main/[organizationId]/products` - Product catalog

**Analytics & Reporting:**
- `/main/[organizationId]/analytics` - Analytics dashboard
- `/main/[organizationId]/reporting` - Reports
- `/main/[organizationId]/reporting/dashboard` - Reporting dashboard
- `/main/[organizationId]/sentiment` - Sentiment analysis
- `/main/[organizationId]/predictive` - Predictive analytics
- `/main/[organizationId]/competitor-analysis` - Competitor analysis
- `/main/[organizationId]/review` - Review management

**AI Tools:**
- `/main/[organizationId]/tools` - AI tools hub
- `/main/[organizationId]/tools/ad-copy` - Ad copy generator
- `/main/[organizationId]/tools/email-template` - Email template generator
- `/main/[organizationId]/tools/image-generator` - Image generator
- `/main/[organizationId]/tools/keyword-research` - Keyword research
- `/main/[organizationId]/tools/label-inspiration` - Label inspiration
- `/main/[organizationId]/tools/product-catalog` - Product catalog generator
- `/main/[organizationId]/tools/seo-analysis` - SEO analysis

**Paid Advertising:**
- `/main/[organizationId]/paid-ads` - Paid ads dashboard
- `/main/[organizationId]/paid-ads/campaigns` - Paid ad campaigns
- `/main/[organizationId]/paid-ads/ad-copy` - Ad copy generator
- `/main/[organizationId]/paid-ads/keyword-research` - Keyword research

**Automation & Workflows:**
- `/main/[organizationId]/automations` - Automation workflows
- `/main/[organizationId]/automations/[workflowId]` - Edit workflow
- `/main/[organizationId]/workflows` - Workflow builder

**Collaboration:**
- `/main/[organizationId]/collaboration` - Team collaboration hub
- `/main/[organizationId]/tasks` - Task management
- `/main/[organizationId]/projects` - Project management
- `/main/[organizationId]/team` - Team members
- `/main/[organizationId]/contacts` - Contact management

**Other Features:**
- `/main/[organizationId]/website-chat` - Website chatbot builder
- `/main/[organizationId]/surveys` - Survey builder
- `/main/[organizationId]/surveys/[surveyId]` - Edit survey
- `/main/[organizationId]/files` - File management
- `/main/[organizationId]/storage-sources` - Storage integrations

**Settings & Billing:**
- `/main/[organizationId]/settings` - Organization settings
- `/main/[organizationId]/billing` - Billing & subscriptions

#### Agency Portal Sitemap

**Base Route:** `/agency/[agencyId]`

**Agency Routes:**
- `/agency/[agencyId]` - Agency dashboard (overview)
- `/agency/[agencyId]/clients` - Client management
- `/agency/[agencyId]/tasks` - Cross-client task board
- `/agency/[agencyId]/calendar` - Aggregated calendar (all clients)
- `/agency/[agencyId]/billing` - Agency billing & invoicing
- `/agency/[agencyId]/reports` - White-label reporting
- `/agency/[agencyId]/team` - Agency team management
- `/agency/[agencyId]/settings` - Agency settings

#### Admin Portal Sitemap

**Base Route:** `/admin`

**Admin Routes:**
- `/admin/login` - Admin login page
- `/admin/dashboard` - Admin dashboard (overview)
- `/admin/organizations` - Organization management
- `/admin/users` - User management
- `/admin/content` - Content moderation
- `/admin/packages` - Subscription package management
- `/admin/costing` - AI usage costing & analytics
- `/admin/billing` - Platform billing overview
- `/admin/team` - Internal team management
- `/admin/logs` - System logs viewer
- `/admin/settings` - Platform settings

#### Route Implementation Examples

**Laravel Route Structure:**

```php
// routes/web.php - Customer/Organization Routes
Route::middleware(['auth', 'verified'])->prefix('main')->name('main.')->group(function () {
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations');
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    
    Route::middleware('organization')->prefix('{organizationId}')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/collaboration', [CollaborationController::class, 'index'])->name('collaboration');
        
        // Campaigns
        Route::prefix('campaigns')->name('campaigns.')->group(function () {
            Route::get('/', [CampaignController::class, 'index'])->name('index');
            Route::get('/create', [CampaignController::class, 'create'])->name('create');
            Route::get('/content', [CampaignContentController::class, 'index'])->name('content');
            Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions');
        });
        
        // Brands
        Route::prefix('brands')->name('brands.')->group(function () {
            Route::get('/', [BrandController::class, 'index'])->name('index');
            Route::get('/choose-name', [BrandNameController::class, 'index'])->name('choose-name');
            Route::get('/ideation', [BrandIdeationController::class, 'index'])->name('ideation');
        });
        
        // ... other route groups
    });
});

// routes/admin.php - Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [Admin\Auth\LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [Admin\Auth\LoginController::class, 'login']);
    });
    
    Route::middleware(['auth:admin', 'role:admin'])->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('organizations', Admin\OrganizationController::class);
        Route::resource('users', Admin\UserController::class);
        Route::get('/content', [Admin\ContentController::class, 'index'])->name('content');
        Route::resource('packages', Admin\PackageController::class);
        Route::get('/costing', [Admin\CostingController::class, 'index'])->name('costing');
        Route::get('/billing', [Admin\BillingController::class, 'index'])->name('billing');
        Route::get('/team', [Admin\TeamController::class, 'index'])->name('team');
        Route::get('/logs', [Admin\LogController::class, 'index'])->name('logs');
        Route::get('/settings', [Admin\SettingsController::class, 'index'])->name('settings');
    });
});

// routes/agency.php - Agency Routes
Route::middleware(['auth', 'role:agency'])->prefix('agency')->name('agency.')->group(function () {
    Route::middleware('agency')->prefix('{agencyId}')->group(function () {
        Route::get('/', [Agency\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/clients', [Agency\ClientController::class, 'index'])->name('clients');
        Route::get('/tasks', [Agency\TaskController::class, 'index'])->name('tasks');
        Route::get('/calendar', [Agency\CalendarController::class, 'index'])->name('calendar');
        Route::get('/billing', [Agency\BillingController::class, 'index'])->name('billing');
        Route::get('/reports', [Agency\ReportController::class, 'index'])->name('reports');
        Route::get('/team', [Agency\TeamController::class, 'index'])->name('team');
        Route::get('/settings', [Agency\SettingsController::class, 'index'])->name('settings');
    });
});
```

**Login Redirect Logic:**

```php
// app/Http/Controllers/Auth/LoginController.php
protected function authenticated(Request $request, $user)
{
    return match($user->user_type) {
        'admin' => redirect()->route('admin.dashboard'),
        'agency' => redirect()->route('agency.dashboard', ['agencyId' => $user->primaryAgency->id]),
        'customer' => redirect()->route('main.organizations'),
        default => redirect()->route('main.organizations'),
    };
}

// app/Http/Controllers/Admin/Auth/LoginController.php
protected function authenticated(Request $request, $user)
{
    return redirect()->route('admin.dashboard');
}
```

**Middleware for Access Control:**

```php
// app/Http/Middleware/EnsureOrganizationAccess.php
class EnsureOrganizationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->route('organizationId');
        $user = $request->user();
        
        if (!$user->hasAccessToOrganization($organizationId)) {
            abort(403, 'You do not have access to this organization.');
        }
        
        return $next($request);
    }
}

// app/Http/Middleware/EnsureAgencyAccess.php
class EnsureAgencyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $agencyId = $request->route('agencyId');
        $user = $request->user();
        
        if (!$user->isAgencyMember($agencyId)) {
            abort(403, 'You do not have access to this agency.');
        }
        
        return $next($request);
    }
}
```

---

### 2. Multi-Tenancy Architecture

#### 2.1 Organization Management
**Features:**
- Create/manage organizations (tenants)
- Organization settings (name, logo, branding, timezone)
- Organization-level feature toggles
- Organization subscription management
- Data isolation per organization

**Database Tables:**
- `organizations` (id, name, slug, logo, timezone, subscription_plan_id, status, trial_ends_at, created_at, updated_at)
- `organization_settings` (id, organization_id, key, value, created_at, updated_at)
- `organization_subscriptions` (id, organization_id, plan_id, status, current_period_start, current_period_end, cancel_at_period_end, created_at, updated_at)

**Laravel Implementation:**
- Laravel Tenancy package (Tenancy for Laravel) or custom scoping
- Global scopes for automatic tenant filtering
- Middleware for tenant identification
- Service classes for tenant operations

#### 2.2 Agency Portal
**Features:**
- Agency account creation and management
- Client organization linking
- Cross-client dashboard
- Unified task management across clients
- White-label reporting
- Agency-level billing

**Database Tables:**
- `agencies` (id, name, owner_id, logo, status, created_at, updated_at)
- `agency_clients` (id, agency_id, organization_id, status, created_at, updated_at)
- `agency_team_members` (id, agency_id, user_id, role, created_at, updated_at)

**Laravel Implementation:**
- Separate agency context middleware
- Agency-scoped queries
- Service classes for agency operations

---

### 3. Dashboard & Analytics

#### 3.1 Centralized Dashboard
**Features:**
- Real-time KPI widgets (campaigns, engagement, revenue)
- Recent activity feed
- Pending tasks overview
- Scheduled content calendar preview
- Quick action buttons
- Customizable widget layout
- Date range filtering

**Database Tables:**
- `dashboard_widgets` (id, user_id, organization_id, widget_type, position, config, created_at, updated_at)
- `activity_logs` (id, organization_id, user_id, action, model_type, model_id, description, ip_address, created_at)

**Laravel Implementation:**
- Livewire components for real-time updates
- Laravel Activity Log package
- Cached dashboard queries
- Event-driven activity logging

#### 3.2 Advanced Analytics Engine
**Features:**
- Campaign performance analytics
- Social media engagement metrics
- ROI calculation and reporting
- Sentiment analysis dashboard
- Competitor comparison charts
- Custom report builder
- Export reports (PDF, Excel, CSV)
- Scheduled report generation

**Database Tables:**
- `analytics_reports` (id, organization_id, name, type, config, generated_at, created_at, updated_at)
- `analytics_metrics` (id, organization_id, metric_type, value, date, metadata, created_at)
- `sentiment_analysis` (id, organization_id, content_id, sentiment_score, sentiment_label, keywords, created_at)

**Laravel Implementation:**
- Laravel Excel for exports
- Queue jobs for report generation
- Cached analytics queries
- Service classes for metric calculations

---

### 4. Campaign Management

#### 4.1 Campaign Creation & Management
**Features:**
- Create multi-channel campaigns
- Campaign templates library
- Campaign goals and KPIs
- Budget allocation per channel
- Campaign timeline visualization
- Campaign status tracking (Draft, Active, Paused, Completed)
- Campaign cloning

**Database Tables:**
- `campaigns` (id, organization_id, name, description, status, start_date, end_date, budget, spent, created_by, created_at, updated_at)
- `campaign_channels` (id, campaign_id, channel_id, budget, spent, status, created_at, updated_at)
- `campaign_goals` (id, campaign_id, metric_type, target_value, current_value, created_at, updated_at)
- `campaign_templates` (id, name, description, config, is_public, created_by, created_at, updated_at)

#### 4.2 Paid Advertising Campaigns
**Features:**
- Separate management for paid ad campaigns (Google Ads, Facebook Ads)
- Budget tracking and spending monitoring
- Performance metrics (impressions, clicks, conversions)
- Campaign status management (Active, Paused, Completed)
- Platform-specific campaign settings

**Database Tables:**
- `paid_campaigns` (id, organization_id, name, platform, budget, spent, status, start_date, end_date, impressions, clicks, conversions, created_at, updated_at)

**Laravel Implementation:**
- Eloquent models with relationships
- Form requests for validation
- Service classes for campaign operations
- Events for campaign status changes

#### 4.2 Content Calendar
**Features:**
- Visual calendar view (month/week/day)
- Drag-and-drop scheduling
- Bulk scheduling operations
- Content approval workflow
- Platform-specific content preview
- Timezone handling
- Recurring content scheduling
- Calendar events and reminders

**Database Tables:**
- `scheduled_posts` (id, organization_id, campaign_id, channel_id, content, scheduled_at, published_at, status, metadata, created_by, created_at, updated_at)
- `content_approvals` (id, scheduled_post_id, approver_id, status, comments, created_at, updated_at)
- `calendar_events` (id, organization_id, user_id, title, description, event_type, start_time, end_time, all_day, location, attendees, created_at, updated_at)

**Laravel Implementation:**
- FullCalendar.js integration
- Laravel Scheduler for publishing
- Queue jobs for scheduled posts
- WebSocket/Pusher for real-time updates

---

### 5. Content Creation & AI Tools

#### 5.1 AI-Powered Content Generation
**Features:**
- Social media post generation (platform-specific)
- Press release generation
- Email template generation
- Blog post generation
- Ad copy generation
- Content variations (A/B testing)
- Brand voice consistency
- Content tone adjustment

**Database Tables:**
- `ai_generations` (id, organization_id, user_id, type, prompt, result, model_used, tokens_used, cost, created_at)
- `content_templates` (id, organization_id, name, type, category, content, variables, is_public, created_by, created_at, updated_at)
- `brand_guidelines` (id, organization_id, brand_id, tone_of_voice, keywords, avoid_keywords, examples, created_at, updated_at)

**Laravel Implementation:**
- Service classes for AI API integration
- Queue jobs for async generation
- Rate limiting for AI requests
- Cost tracking and billing integration
- Caching for common prompts

#### 5.2 Image Generation
**Features:**
- Text-to-image generation
- Image style presets
- Image editing and enhancement
- Image library management
- Royalty-free image generation
- Image optimization for platforms

**Database Tables:**
- `generated_images` (id, organization_id, user_id, prompt, image_url, style, model_used, cost, created_at)
- `image_library` (id, organization_id, name, url, tags, size, created_at)

**Laravel Implementation:**
- File storage integration
- Image processing with Intervention Image
- Queue jobs for generation
- CDN integration for delivery

#### 5.3 SEO Tools
**Features:**
- Keyword research and suggestions
- SEO content analysis
- Meta tag generation
- Sitemap generation
- Competitor SEO analysis
- Content optimization recommendations

**Database Tables:**
- `seo_analyses` (id, organization_id, url, score, recommendations, keywords, created_at, updated_at)
- `keyword_research` (id, organization_id, keyword, search_volume, difficulty, competition, created_at)

**Laravel Implementation:**
- External SEO API integration
- Service classes for SEO operations
- Cached keyword data
- Scheduled SEO audits

---

### 6. Social Media Integration

#### 6.1 Platform Connections
**Features:**
- OAuth integration for platforms (Facebook, Instagram, LinkedIn, Twitter, TikTok, Pinterest)
- Connection status monitoring
- Token refresh management
- Multi-account support per platform
- Platform-specific settings

**Database Tables:**
- `social_connections` (id, organization_id, platform, account_id, account_name, access_token, refresh_token, expires_at, status, created_at, updated_at)
- `platform_settings` (id, connection_id, settings, created_at, updated_at)

#### 6.2 Channel Management
**Features:**
- Unified channel management system
- Support for multiple channel types (Email, WhatsApp, Amplify, Paid Ads, Press Release, Influencer)
- Channel-specific configurations
- Channel status tracking

**Database Tables:**
- `channels` (id, organization_id, display_name, type, platform, status, created_at, updated_at)
- `channel_settings` (id, channel_id, settings_json, created_at, updated_at)
- `influencer_channels` (id, channel_id, influencer_name, follower_count, engagement_rate, created_at, updated_at)

**Laravel Implementation:**
- Socialite package for OAuth
- Encrypted token storage
- Queue jobs for token refresh
- Service classes per platform

#### 6.2 Post Publishing
**Features:**
- Direct publishing to platforms
- Scheduled publishing
- Multi-platform posting
- Post preview before publishing
- Publishing error handling and retry
- Publishing history

**Database Tables:**
- `published_posts` (id, scheduled_post_id, connection_id, platform_post_id, status, published_at, engagement_metrics, created_at, updated_at)
- `publishing_errors` (id, scheduled_post_id, error_message, retry_count, created_at)

**Laravel Implementation:**
- Queue jobs for publishing
- Retry logic for failed posts
- Webhook handlers for platform callbacks
- Event listeners for post status updates

---

### 7. Email Marketing

#### 7.1 Email Campaigns
**Features:**
- Create email campaigns
- Email template builder (drag-and-drop)
- Contact list management
- Segmentation and targeting
- A/B testing
- Send scheduling
- Open/click tracking
- Unsubscribe management

**Database Tables:**
- `email_campaigns` (id, organization_id, name, subject, content, status, scheduled_at, sent_at, recipient_count, open_count, click_count, created_by, created_at, updated_at)
- `email_templates` (id, organization_id, name, subject, content, type, created_at, updated_at)
- `contact_lists` (id, organization_id, name, contact_count, created_at, updated_at)
- `campaign_recipients` (id, campaign_id, contact_id, status, opened_at, clicked_at, unsubscribed_at, created_at)
- `email_tracking` (id, campaign_id, recipient_id, event_type, ip_address, user_agent, created_at)

**Laravel Implementation:**
- Laravel Mail for sending
- Queue jobs for bulk sending
- Tracking pixels and link tracking
- Service classes for campaign operations

#### 7.2 Contact Management
**Features:**
- Import contacts (CSV, Excel)
- Contact tagging and segmentation
- Contact activity history
- Duplicate detection and merging
- GDPR compliance (data export/deletion)

**Database Tables:**
- `contacts` (id, organization_id, email, first_name, last_name, phone, tags, status, subscribed_at, unsubscribed_at, created_at, updated_at)
- `contact_tags` (id, organization_id, name, color, created_at, updated_at)
- `contact_list_contacts` (contact_id, list_id, added_at)
- `contact_activities` (id, contact_id, activity_type, description, created_at)

**Laravel Implementation:**
- Import jobs for bulk operations
- Eloquent relationships
- Service classes for contact operations

---

### 8. Press Release Management

#### 8.1 Press Release Creation
**Features:**
- AI-assisted press release writing
- Press release templates
- Media contact directory
- Distribution list management
- Press release scheduling
- Distribution tracking

**Database Tables:**
- `press_releases` (id, organization_id, title, content, status, scheduled_at, distributed_at, created_by, created_at, updated_at)
- `press_contacts` (id, organization_id, name, outlet, email, phone, beat, relationship_status, last_contact_at, created_at, updated_at)
- `press_distributions` (id, press_release_id, contact_id, status, sent_at, opened_at, created_at)

**Laravel Implementation:**
- Service classes for press release operations
- Queue jobs for distribution
- Email integration for sending

---

### 9. Brand Management

#### 9.1 Brand Profiles
**Features:**
- Create and manage brand profiles
- Brand guidelines (tone, voice, keywords)
- Brand asset library (logos, images, fonts)
- Brand name generator with availability checks
- Brand ideation tools
- Multi-brand support per organization

**Database Tables:**
- `brands` (id, organization_id, name, summary, audience, guidelines, tone_of_voice, keywords, avoid_keywords, logo, status, business_model, created_at, updated_at)
- `brand_assets` (id, brand_id, name, type, url, tags, created_at, updated_at)
- `brand_name_suggestions` (id, organization_id, brand_id, name, domain_available, social_handles_available, score, created_at)

**Laravel Implementation:**
- Service classes for brand operations
- External API integration for domain/handle checks
- File storage for assets

---

### 10. Product Catalog

#### 10.1 Product Management
**Features:**
- Product CRUD operations
- Product categorization
- Bulk import/export (CSV, Excel)
- Product images and media
- Inventory tracking
- Product variants
- Product linking to campaigns

**Database Tables:**
- `products` (id, organization_id, brand_id, name, sku, category, price, stock, status, description, image, metadata, created_at, updated_at)
- `product_categories` (id, organization_id, name, parent_id, created_at, updated_at)
- `product_images` (id, product_id, url, order, created_at)
- `product_variants` (id, product_id, name, sku, price, stock, created_at, updated_at)

**Laravel Implementation:**
- Eloquent models with relationships
- Import/export jobs
- Service classes for product operations

---

### 11. Competitor Analysis

#### 11.1 Competitor Tracking
**Features:**
- Add/manage competitors
- Competitor social media tracking
- Competitor content analysis
- Performance comparison
- Competitive intelligence reports
- Automated competitor monitoring

**Database Tables:**
- `competitors` (id, organization_id, name, website, description, social_profiles, created_at, updated_at)
- `competitor_analyses` (id, competitor_id, analysis_type, data, analyzed_at, created_at)
- `competitor_posts` (id, competitor_id, platform, content, engagement, posted_at, created_at)

**Laravel Implementation:**
- Queue jobs for competitor scraping
- Service classes for analysis
- Scheduled jobs for monitoring

---

### 12. Automation & Workflows

#### 12.1 Workflow Builder
**Features:**
- Visual workflow builder (drag-and-drop)
- Trigger-based automation (if-this-then-that)
- Action templates library
- Workflow testing and debugging
- Workflow scheduling
- Workflow execution history

**Database Tables:**
- `workflows` (id, organization_id, name, description, trigger_type, trigger_config, actions, status, created_by, created_at, updated_at)
- `workflow_executions` (id, workflow_id, status, input_data, output_data, executed_at, created_at)
- `workflow_triggers` (id, workflow_id, type, config, created_at, updated_at)
- `workflow_actions` (id, workflow_id, type, config, order, created_at, updated_at)

**Laravel Implementation:**
- Queue jobs for workflow execution
- Event-driven triggers
- Service classes for workflow operations
- Workflow engine service

#### 12.2 Automation Rules
**Features:**
- Pre-defined automation templates
- Custom automation creation
- Conditional logic builder
- Multi-step automation chains
- Automation pause/resume

**Database Tables:**
- `automation_rules` (id, organization_id, name, conditions, actions, status, created_at, updated_at)
- `automation_executions` (id, rule_id, status, executed_at, created_at)

**Laravel Implementation:**
- Rule engine service
- Queue jobs for execution
- Event listeners for triggers

---

### 13. Website Chatbot

#### 13.1 Chatbot Builder
**Features:**
- Create and configure chatbots
- Custom training data
- Conversation flow builder
- Multi-language support
- Lead capture forms
- Analytics and reporting
- Embed code generation

**Database Tables:**
- `chatbots` (id, organization_id, name, description, model, training_data, config, embed_code, status, created_at, updated_at)
- `chatbot_conversations` (id, chatbot_id, session_id, messages, metadata, started_at, ended_at, created_at)
- `chatbot_leads` (id, chatbot_id, conversation_id, name, email, phone, data, created_at)

**Laravel Implementation:**
- AI service integration
- WebSocket for real-time chat
- Service classes for chatbot operations
- Queue jobs for lead processing

---

### 14. Landing Page Builder

#### 14.1 Page Creation
**Features:**
- Drag-and-drop page builder
- AI-powered page generation
- Responsive design
- Template library
- Custom domain support
- SEO optimization
- A/B testing

**Database Tables:**
- `landing_pages` (id, organization_id, campaign_id, name, slug, content, template, seo_settings, status, published_at, created_by, created_at, updated_at)
- `landing_page_variants` (id, landing_page_id, name, content, traffic_percentage, created_at, updated_at)
- `page_analytics` (id, landing_page_id, variant_id, views, conversions, conversion_rate, date, created_at)

**Laravel Implementation:**
- Service classes for page operations
- Queue jobs for publishing
- Analytics tracking

---

### 15. Surveys & Feedback

#### 15.1 Survey Builder
**Features:**
- Create surveys with multiple question types
- Survey distribution (email, link, embed)
- Response collection and analysis
- Survey analytics
- Export responses

**Database Tables:**
- `surveys` (id, organization_id, name, description, questions, status, created_by, created_at, updated_at)
- `survey_responses` (id, survey_id, respondent_email, answers, submitted_at, created_at)
- `survey_questions` (id, survey_id, question, type, options, required, order, created_at)

**Laravel Implementation:**
- Service classes for survey operations
- Queue jobs for distribution
- Analytics calculations

---

### 16. Task Management

#### 16.1 Task System
**Features:**
- Create and assign tasks
- Task status tracking
- Priority levels
- Due dates and reminders
- Task comments and attachments
- Task templates
- Cross-organization task management (agencies)

**Database Tables:**
- `tasks` (id, organization_id, agency_id, title, description, status, priority, due_date, assignee_id, created_by, created_at, updated_at)
- `task_comments` (id, task_id, user_id, comment, created_at)
- `task_attachments` (id, task_id, name, url, created_at)
- `task_templates` (id, organization_id, name, description, config, created_at, updated_at)

#### 16.2 Project Management
**Features:**
- Create and manage projects
- Project status tracking (Planning, In Progress, Review, Completed)
- Project progress tracking
- Team member assignment
- Project workflow states
- Client association

**Database Tables:**
- `projects` (id, organization_id, name, client, status, progress, due_date, created_by, created_at, updated_at)
- `project_members` (id, project_id, user_id, role, created_at)
- `workflow_states` (id, organization_id, name, color, is_active, order, created_at, updated_at)

**Laravel Implementation:**
- Service classes for task operations
- Notifications for assignments
- Queue jobs for reminders

---

### 17. Collaboration Tools

#### 17.1 Live Chat
**Features:**
- Real-time team chat
- Chat topics/channels
- File sharing
- Message reactions
- @mentions
- Chat history

**Database Tables:**
- `chat_topics` (id, organization_id, name, description, type, created_by, created_at, updated_at)
- `chat_messages` (id, topic_id, user_id, content, parent_id, created_at, updated_at)
- `chat_reactions` (id, message_id, user_id, emoji, created_at)
- `chat_participants` (topic_id, user_id, last_read_at, joined_at)

#### 17.3 Notifications System
**Features:**
- In-app notifications
- Notification preferences per user
- Notification types (info, success, warning, error)
- Read/unread status tracking
- Notification history

**Database Tables:**
- `notifications` (id, organization_id, user_id, title, message, type, is_read, link, metadata, created_at, updated_at)
- `notification_preferences` (id, user_id, notification_type, email_enabled, push_enabled, in_app_enabled, created_at, updated_at)

**Laravel Implementation:**
- Laravel Echo + Pusher/Broadcasting
- Real-time event broadcasting
- Service classes for chat operations

#### 17.2 Content Approval Workflow
**Features:**
- Content review and approval
- Multi-level approvals
- Approval comments
- Approval notifications
- Approval history

**Database Tables:**
- `content_approvals` (id, content_id, content_type, approver_id, status, comments, approved_at, created_at)
- `approval_workflows` (id, organization_id, name, steps, created_at, updated_at)

**Laravel Implementation:**
- Service classes for approval operations
- Notifications for approvals
- Queue jobs for workflow processing

---

### 18. Billing & Subscriptions

#### 18.1 Subscription Management
**Features:**
- Multiple subscription plans
- Plan upgrades/downgrades
- Trial periods
- Usage-based billing
- Invoice generation
- Payment processing (Stripe/PayPal)
- Subscription cancellation

**Database Tables:**
- `subscription_plans` (id, name, slug, price, billing_cycle, features, limits, is_active, created_at, updated_at)
- `subscriptions` (id, organization_id, plan_id, status, current_period_start, current_period_end, cancel_at_period_end, trial_ends_at, created_at, updated_at)
- `invoices` (id, organization_id, subscription_id, amount, tax, total, status, due_date, paid_at, created_at, updated_at)
- `invoice_items` (id, invoice_id, description, quantity, price, total, created_at)
- `payments` (id, invoice_id, amount, payment_method, transaction_id, status, created_at)

**Laravel Implementation:**
- Laravel Cashier (Stripe) or custom implementation
- Queue jobs for invoice generation
- Webhook handlers for payment events
- Service classes for billing operations

#### 18.2 Usage Tracking & Cost Analytics
**Features:**
- AI usage tracking (tokens, API calls)
- Cost calculation per organization
- Usage reports
- Cost breakdown by feature
- Budget alerts

**Database Tables:**
- `usage_tracking` (id, organization_id, feature_type, usage_count, cost, date, metadata, created_at)
- `ai_usage_logs` (id, organization_id, user_id, operation_type, model, tokens_used, cost, created_at)
- `usage_limits` (id, organization_id, feature_type, limit_value, current_usage, reset_period, created_at, updated_at)

**Laravel Implementation:**
- Middleware for usage tracking
- Service classes for cost calculation
- Scheduled jobs for limit resets
- Queue jobs for cost aggregation

---

### 19. Reporting & Analytics

#### 19.1 Custom Reports
**Features:**
- Report builder (drag-and-drop)
- Multiple chart types
- Data filtering and grouping
- Scheduled report generation
- Report sharing
- White-label reports (agencies)
- Export formats (PDF, Excel, CSV)

**Database Tables:**
- `reports` (id, organization_id, name, type, config, schedule, last_generated_at, created_by, created_at, updated_at)
- `report_schedules` (id, report_id, frequency, next_run_at, last_run_at, created_at, updated_at)
- `report_shares` (id, report_id, shared_with_user_id, permissions, created_at)

**Laravel Implementation:**
- Laravel Excel for exports
- Queue jobs for report generation
- Service classes for report operations
- PDF generation (DomPDF/Snappy)

#### 19.2 Sentiment Analysis
**Features:**
- Automated sentiment analysis
- Social media sentiment tracking
- Review sentiment analysis
- Sentiment trends over time
- Sentiment alerts

**Database Tables:**
- `sentiment_analyses` (id, organization_id, content_id, content_type, sentiment_score, sentiment_label, keywords, analyzed_at, created_at)
- `sentiment_trends` (id, organization_id, date, average_sentiment, positive_count, negative_count, neutral_count, created_at)

#### 19.4 Reviews Management
**Features:**
- Collect and manage customer reviews
- Review source tracking (Google, Amazon, Trustpilot, etc.)
- Review sentiment analysis
- Review response management
- Review aggregation and reporting

**Database Tables:**
- `reviews` (id, organization_id, brand_id, content, platform, rating, author, author_email, date, sentiment, status, created_at, updated_at)
- `review_sources` (id, organization_id, name, platform, api_key, status, created_at, updated_at)
- `review_responses` (id, review_id, user_id, response_content, published_at, created_at, updated_at)

**Laravel Implementation:**
- AI service integration
- Queue jobs for analysis
- Service classes for sentiment operations

#### 19.3 Predictive Analytics
**Features:**
- Campaign performance prediction
- Content engagement forecasting
- ROI prediction
- Optimal posting time suggestions
- Budget optimization recommendations

**Database Tables:**
- `predictions` (id, organization_id, prediction_type, input_data, predicted_value, confidence_score, created_at)
- `prediction_models` (id, organization_id, model_type, training_data, accuracy, created_at, updated_at)

**Laravel Implementation:**
- ML service integration
- Service classes for predictions
- Queue jobs for model training

---

### 20. Admin Portal

#### 20.1 User Management
**Features:**
- View all users
- Edit user details
- Assign roles
- Deactivate/reactivate users
- User activity logs
- User search and filtering

**Database Tables:**
- (Uses existing `users` and `user_roles` tables)

**Laravel Implementation:**
- Admin controllers
- Service classes for user operations
- Activity logging

#### 20.2 Content Moderation
**Features:**
- Review user-generated content
- Flag inappropriate content
- Content approval/rejection
- Content deletion
- Moderation queue

**Database Tables:**
- `content_flags` (id, content_id, content_type, reason, flagged_by, status, reviewed_by, reviewed_at, created_at)
- `moderation_queue` (id, content_id, content_type, priority, status, created_at, updated_at)

**Laravel Implementation:**
- Admin controllers
- Queue jobs for moderation
- Service classes for moderation operations

#### 20.3 Platform Settings
**Features:**
- Global platform settings
- Feature toggles
- API key management
- Maintenance mode
- System logs viewing
- Performance monitoring

**Database Tables:**
- `platform_settings` (id, key, value, type, description, created_at, updated_at)
- `system_logs` (id, level, message, context, user_id, created_at)
- `feature_flags` (id, name, description, enabled, created_at, updated_at)

**Laravel Implementation:**
- Admin controllers
- Cached settings
- Laravel Telescope integration
- Service classes for settings

---

## Database Schema

### Core Tables Summary

**Users & Authentication:**
- users
- user_sessions
- password_reset_tokens
- email_verification_tokens
- roles
- permissions
- role_permissions
- user_roles

**Multi-Tenancy:**
- organizations
- organization_settings
- organization_subscriptions
- agencies
- agency_clients
- agency_team_members

**Content & Campaigns:**
- campaigns
- campaign_channels
- campaign_goals
- scheduled_posts
- content_approvals
- press_releases
- press_contacts
- press_distributions
- paid_campaigns
- channels
- channel_settings

**Brands & Products:**
- brands
- brand_assets
- products
- product_categories
- product_images
- product_variants

**Social Media:**
- social_connections
- platform_settings
- published_posts
- publishing_errors

**Email Marketing:**
- email_campaigns
- email_templates
- contact_lists
- contact_list_contacts
- contacts
- contact_tags
- contact_activities
- campaign_recipients
- email_tracking

**AI & Automation:**
- ai_generations
- generated_images
- workflows
- workflow_executions
- automation_rules
- automation_executions

**Analytics & Reporting:**
- analytics_reports
- analytics_metrics
- sentiment_analyses
- predictions
- reports
- report_schedules

**Billing:**
- subscription_plans
- subscriptions
- invoices
- invoice_items
- payments
- usage_tracking
- ai_usage_logs

**Collaboration:**
- chat_topics
- chat_messages
- chat_reactions
- chat_participants
- tasks
- task_comments
- task_attachments
- projects
- project_members
- notifications
- notification_preferences

**Reviews & Feedback:**
- reviews
- review_sources
- review_responses

**Other:**
- chatbots
- chatbot_conversations
- chatbot_leads
- landing_pages
- landing_page_variants
- page_analytics
- surveys
- survey_questions
- survey_responses
- competitors
- competitor_analyses
- competitor_posts
- calendar_events
- workflow_states
- content_templates
- testimonials
- dashboard_widgets
- activity_logs

### Database Relationships

**Key Relationships:**
- Organizations → Users (many-to-many via user_roles)
- Organizations → Brands (one-to-many)
- Organizations → Campaigns (one-to-many)
- Organizations → Paid Campaigns (one-to-many)
- Campaigns → Scheduled Posts (one-to-many)
- Campaigns → Channels (many-to-many via campaign_channels)
- Brands → Products (one-to-many)
- Brands → Reviews (one-to-many)
- Users → Tasks (assignee/creator)
- Users → Notifications (one-to-many)
- Organizations → Social Connections (one-to-many)
- Campaigns → Email Campaigns (one-to-many)
- Projects → Users (many-to-many via project_members)
- Contact Lists → Contacts (many-to-many via contact_list_contacts)
- Chat Topics → Users (many-to-many via chat_participants)

### Indexing Strategy

**Critical Indexes:**
- `organizations.id` (primary)
- `users.email` (unique)
- `scheduled_posts.scheduled_at` (for scheduler queries)
- `scheduled_posts.organization_id` (for tenant scoping)
- `published_posts.scheduled_post_id` (for joins)
- `usage_tracking.organization_id, date` (composite for reports)
- `chat_messages.topic_id, created_at` (composite for chat queries)
- `notifications.user_id, is_read, created_at` (composite for user notifications)
- `tasks.organization_id, assignee_id, status` (composite for task queries)
- `campaigns.organization_id, status, start_date` (composite for campaign queries)

### Complete Table Reference

**Total Tables: 80+**

**Authentication & Users (8 tables):**
1. users
2. user_sessions
3. password_reset_tokens
4. email_verification_tokens
5. roles
6. permissions
7. role_permissions
8. user_roles

**Multi-Tenancy (6 tables):**
9. organizations
10. organization_settings
11. organization_subscriptions
12. agencies
13. agency_clients
14. agency_team_members

**Campaigns & Content (11 tables):**
15. campaigns
16. campaign_channels
17. campaign_goals
18. campaign_templates
19. paid_campaigns
20. scheduled_posts
21. content_approvals
22. approval_workflows
23. calendar_events
24. content_templates
25. content_flags

**Channels (3 tables):**
26. channels
27. channel_settings
28. influencer_channels

**Social Media (4 tables):**
29. social_connections
30. platform_settings
31. published_posts
32. publishing_errors

**Email Marketing (9 tables):**
33. email_campaigns
34. email_templates
35. contact_lists
36. contact_list_contacts
37. contacts
38. contact_tags
39. contact_activities
40. campaign_recipients
41. email_tracking

**Brands & Products (5 tables):**
42. brands
43. brand_assets
44. brand_name_suggestions
45. products
46. product_categories
47. product_images
48. product_variants

**Press Releases (3 tables):**
49. press_releases
50. press_contacts
51. press_distributions

**AI & Automation (6 tables):**
52. ai_generations
53. generated_images
54. image_library
55. workflows
56. workflow_executions
57. workflow_triggers
58. workflow_actions
59. automation_rules
60. automation_executions

**Analytics & Reporting (8 tables):**
61. analytics_reports
62. analytics_metrics
63. sentiment_analyses
64. sentiment_trends
65. predictions
66. prediction_models
67. reports
68. report_schedules
69. report_shares

**Reviews (3 tables):**
70. reviews
71. review_sources
72. review_responses

**Billing (7 tables):**
73. subscription_plans
74. subscriptions
75. invoices
76. invoice_items
77. payments
78. usage_tracking
79. ai_usage_logs
80. usage_limits

**Collaboration (10 tables):**
81. chat_topics
82. chat_messages
83. chat_reactions
84. chat_participants
85. tasks
86. task_comments
87. task_attachments
88. task_templates
89. projects
90. project_members
91. workflow_states

**Notifications (2 tables):**
92. notifications
93. notification_preferences

**Chatbots (3 tables):**
94. chatbots
95. chatbot_conversations
96. chatbot_leads

**Landing Pages (3 tables):**
97. landing_pages
98. landing_page_variants
99. page_analytics

**Surveys (3 tables):**
100. surveys
101. survey_questions
102. survey_responses

**Competitors (3 tables):**
103. competitors
104. competitor_analyses
105. competitor_posts

**SEO (2 tables):**
106. seo_analyses
107. keyword_research

**Dashboard (2 tables):**
108. dashboard_widgets
109. activity_logs

**Admin (4 tables):**
110. moderation_queue
111. platform_settings
112. system_logs
113. feature_flags

**Other (1 table):**
114. testimonials

---

## API Specifications

### RESTful API Endpoints

**Authentication:**
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `POST /api/auth/refresh` - Refresh token
- `POST /api/auth/forgot-password` - Password reset request
- `POST /api/auth/reset-password` - Password reset

**Organizations:**
- `GET /api/organizations` - List organizations
- `POST /api/organizations` - Create organization
- `GET /api/organizations/{id}` - Get organization
- `PUT /api/organizations/{id}` - Update organization
- `DELETE /api/organizations/{id}` - Delete organization

**Campaigns:**
- `GET /api/campaigns` - List campaigns
- `POST /api/campaigns` - Create campaign
- `GET /api/campaigns/{id}` - Get campaign
- `PUT /api/campaigns/{id}` - Update campaign
- `DELETE /api/campaigns/{id}` - Delete campaign
- `POST /api/campaigns/{id}/publish` - Publish campaign

**Content:**
- `GET /api/content` - List content
- `POST /api/content` - Create content
- `GET /api/content/{id}` - Get content
- `PUT /api/content/{id}` - Update content
- `DELETE /api/content/{id}` - Delete content
- `POST /api/content/{id}/approve` - Approve content
- `POST /api/content/{id}/reject` - Reject content

**AI Generation:**
- `POST /api/ai/generate/social-post` - Generate social post
- `POST /api/ai/generate/press-release` - Generate press release
- `POST /api/ai/generate/email` - Generate email
- `POST /api/ai/generate/image` - Generate image
- `POST /api/ai/analyze/sentiment` - Analyze sentiment
- `POST /api/ai/analyze/seo` - Analyze SEO

**Social Media:**
- `GET /api/social/connections` - List connections
- `POST /api/social/connections` - Create connection
- `DELETE /api/social/connections/{id}` - Delete connection
- `POST /api/social/publish` - Publish post
- `GET /api/social/analytics` - Get analytics

**Analytics:**
- `GET /api/analytics/dashboard` - Dashboard metrics
- `GET /api/analytics/campaigns/{id}` - Campaign analytics
- `GET /api/analytics/reports` - List reports
- `POST /api/analytics/reports` - Create report
- `GET /api/analytics/reports/{id}` - Get report

### API Authentication

- **Method**: Laravel Sanctum (token-based)
- **Headers**: `Authorization: Bearer {token}`
- **Rate Limiting**: 60 requests per minute per user
- **API Versioning**: `/api/v1/` prefix

### API Response Format

```json
{
  "success": true,
  "data": {},
  "message": "Operation successful",
  "meta": {
    "pagination": {}
  }
}
```

### Error Response Format

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Error message",
    "details": {}
  }
}
```

---

## Security Requirements

### Authentication & Authorization
- **Password Requirements**: Minimum 8 characters, mixed case, numbers, special characters
- **Session Management**: Secure session cookies, session timeout (2 hours inactivity)
- **MFA Support**: TOTP-based (Google Authenticator, Authy)
- **API Security**: Token-based authentication, token expiration (24 hours), refresh tokens
- **Role-Based Access**: Hierarchical permissions, resource-level authorization

### Data Protection
- **Encryption**: Database encryption at rest, TLS for data in transit
- **Sensitive Data**: Encrypt tokens, API keys, payment information
- **GDPR Compliance**: Data export, right to deletion, consent management
- **Data Isolation**: Strict tenant isolation, no cross-tenant data access

### Input Validation
- **Sanitization**: All user input sanitized, HTML sanitization for rich content
- **Validation**: Server-side validation for all inputs, CSRF protection
- **File Uploads**: MIME type validation, file size limits, virus scanning
- **SQL Injection**: Parameterized queries, Eloquent ORM usage

### Security Headers
- **CSP**: Content Security Policy headers
- **XSS Protection**: X-XSS-Protection header
- **Frame Options**: X-Frame-Options: DENY
- **Content Type**: X-Content-Type-Options: nosniff
- **HSTS**: Strict-Transport-Security header

### Rate Limiting
- **API Endpoints**: 60 requests/minute per user
- **Authentication**: 5 attempts/minute per IP
- **AI Generation**: Based on subscription plan
- **Email Sending**: Based on subscription plan

### Audit Logging
- **User Actions**: Log all critical user actions
- **Admin Actions**: Comprehensive admin action logging
- **Data Changes**: Track all data modifications
- **Security Events**: Log failed login attempts, permission denials

---

## Performance Requirements

### Response Times
- **Page Load**: < 2 seconds (first contentful paint)
- **API Response**: < 500ms (95th percentile)
- **Dashboard Load**: < 3 seconds
- **Search Results**: < 1 second

### Scalability
- **Concurrent Users**: Support 10,000+ concurrent users
- **Database**: Optimize queries, use indexes, connection pooling
- **Caching**: Redis caching for frequently accessed data
- **Queue Processing**: Async processing for heavy operations

### Optimization Strategies
- **Database**: Query optimization, eager loading, database indexing
- **Caching**: Redis for sessions, queries, API responses
- **CDN**: Static asset delivery via CDN
- **Image Optimization**: Image compression, lazy loading, responsive images
- **Code Splitting**: Frontend code splitting for faster initial load
- **Lazy Loading**: Lazy load components and data

### Monitoring
- **Application Monitoring**: Laravel Telescope, Sentry
- **Performance Monitoring**: APM tools (New Relic, Datadog)
- **Database Monitoring**: Query performance tracking
- **Server Monitoring**: CPU, memory, disk usage

---

## Integration Requirements

### Payment Gateways
- **Stripe**: Subscription management, payment processing, webhooks
- **PayPal**: Alternative payment method, webhook handling

### Email Services
- **SendGrid**: Transactional emails, marketing emails
- **Mailgun**: Alternative email service
- **AWS SES**: Cost-effective email delivery

### Social Media APIs
- **Facebook Graph API**: Posting, analytics, page management
- **Instagram Basic Display API**: Posting, media management
- **LinkedIn API**: Posting, company page management
- **Twitter API v2**: Posting, analytics
- **TikTok API**: Posting, analytics
- **Pinterest API**: Pin creation, analytics

### AI Services
- **Google Gemini AI**: Content generation, analysis
- **OpenAI API**: Alternative AI provider
- **DALL-E / Midjourney**: Image generation

### Analytics Services
- **Google Analytics**: Website tracking
- **Facebook Pixel**: Conversion tracking
- **Custom Analytics**: Internal analytics system

### Storage Services
- **AWS S3**: File storage, backups
- **Cloudflare R2**: Alternative storage
- **Local Storage**: Development environment

### Communication Services
- **Pusher / Laravel Echo**: Real-time updates
- **Twilio**: SMS notifications (optional)
- **WhatsApp Business API**: WhatsApp integration

---

## Deployment Specifications

### Server Requirements
- **PHP**: 8.3+
- **Web Server**: Nginx 1.24+ or Apache 2.4+
- **Database**: MySQL 8.0+ or PostgreSQL 15+
- **Cache**: Redis 7.0+
- **Queue**: Redis or database queue
- **Storage**: Minimum 100GB SSD

### Environment Configuration
- **Development**: Local environment with Docker
- **Staging**: Staging server matching production
- **Production**: High-availability setup with load balancing

### Deployment Process
1. **Code Deployment**: Git-based deployment (GitHub Actions, GitLab CI/CD)
2. **Database Migrations**: Automated migration on deployment
3. **Asset Compilation**: Frontend asset compilation (Vite/Mix)
4. **Cache Clearing**: Clear application cache post-deployment
5. **Queue Restart**: Restart queue workers
6. **Health Checks**: Verify deployment success

### Backup Strategy
- **Database Backups**: Daily automated backups, 30-day retention
- **File Backups**: Daily backups of uploaded files
- **Configuration Backups**: Version-controlled configuration
- **Disaster Recovery**: Tested recovery procedures

### Monitoring & Logging
- **Application Logs**: Laravel logging to files/cloud
- **Error Tracking**: Sentry integration
- **Performance Monitoring**: APM tools
- **Uptime Monitoring**: External monitoring services

---

## Migration Strategy

### Phase 1: Foundation (Weeks 1-4)
- Set up Laravel 12 project structure
- Implement authentication system
- Create database schema
- Set up multi-tenancy architecture
- Implement basic admin portal

### Phase 2: Core Features (Weeks 5-8)
- Campaign management system
- Content creation tools
- Social media integration
- Basic AI integration
- Email marketing module

### Phase 3: Advanced Features (Weeks 9-12)
- Advanced analytics
- Automation workflows
- Website chatbot
- Landing page builder
- Competitor analysis

### Phase 4: Agency Features (Weeks 13-14)
- Agency portal
- Client management
- White-label reporting
- Cross-client task management

### Phase 5: Polish & Testing (Weeks 15-16)
- Performance optimization
- Security hardening
- Comprehensive testing
- Documentation
- User training materials

### Phase 6: Migration & Launch (Weeks 17-18)
- Data migration from existing system
- Staging environment testing
- Production deployment
- Post-launch monitoring
- Bug fixes and adjustments

### Migration Considerations
- **Data Migration**: Export from Firestore, import to MySQL/PostgreSQL
- **User Migration**: Migrate user accounts and authentication
- **Content Migration**: Migrate campaigns, posts, and content
- **Downtime**: Minimize downtime with parallel systems
- **Rollback Plan**: Ability to rollback if issues occur

---

## Additional Considerations

### Code Quality Standards
- **PSR-12**: PHP coding standards
- **SOLID Principles**: Object-oriented design principles
- **DRY Principle**: Don't repeat yourself
- **Code Reviews**: Mandatory code reviews
- **Testing**: Unit tests, feature tests, integration tests

### Documentation Requirements
- **API Documentation**: Auto-generated API docs
- **Code Documentation**: PHPDoc comments
- **User Guides**: End-user documentation
- **Developer Guides**: Technical documentation
- **Deployment Guides**: Deployment procedures

### Support & Maintenance
- **Bug Tracking**: Issue tracking system
- **Version Control**: Git-based version control
- **Release Management**: Semantic versioning
- **Hotfix Process**: Emergency fix procedures
- **Update Process**: Regular update schedule

---

## Conclusion

This comprehensive specification document outlines all features, technical requirements, and implementation details for redeveloping MarketPulse in Laravel 12. The modular approach allows for phased development and incremental releases while maintaining system stability and user experience.

**Key Success Factors:**
1. Robust multi-tenancy architecture
2. Scalable AI integration
3. Comprehensive security measures
4. Performance optimization
5. Seamless user experience
6. Reliable deployment process

**Next Steps:**
1. Review and approve specifications
2. Set up development environment
3. Begin Phase 1 implementation
4. Establish development workflow
5. Create project timeline and milestones

---

*Document Version: 1.0*  
*Last Updated: [Current Date]*  
*Prepared for: MarketPulse Laravel 12 Redevelopment*

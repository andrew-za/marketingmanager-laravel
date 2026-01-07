# MarketPulse - Model Relationships Documentation

This document provides a comprehensive overview of all Eloquent model relationships in the MarketPulse Laravel application. Understanding these relationships is crucial for querying data efficiently and maintaining data integrity.

## Table of Contents
1. [Relationship Types Overview](#relationship-types-overview)
2. [Core Models & Relationships](#core-models--relationships)
3. [Relationship Diagrams](#relationship-diagrams)
4. [Query Examples](#query-examples)
5. [Eager Loading Strategies](#eager-loading-strategies)

---

## Relationship Types Overview

### Relationship Types Used
- **hasOne**: One-to-one relationship
- **hasMany**: One-to-many relationship
- **belongsTo**: Many-to-one relationship
- **belongsToMany**: Many-to-many relationship (with pivot table)
- **morphTo / morphMany**: Polymorphic relationships
- **hasManyThrough**: Has many through relationship

---

## Core Models & Relationships

### 1. User Model

**Table:** `users`

**Relationships:**

```php
class User extends Authenticatable
{
    // Many-to-Many: User belongs to many organizations (through user_roles)
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'user_roles')
            ->withPivot('role_id')
            ->withTimestamps();
    }
    
    // Note: Roles are handled by Spatie Permission package via HasRoles trait
    
    // One-to-Many: User created many campaigns
    public function createdCampaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'created_by');
    }
    
    // One-to-Many: User has many scheduled posts
    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class, 'created_by');
    }
    
    // One-to-Many: User has many tasks (as assignee)
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }
    
    // One-to-Many: User created many tasks
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }
    
    // One-to-Many: User has many AI generations
    public function aiGenerations(): HasMany
    {
        return $this->hasMany(AiGeneration::class);
    }
    
    // Many-to-Many: User belongs to many agencies
    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(Agency::class, 'agency_team_members')
            ->withPivot('role')
            ->withTimestamps();
    }
    
    // One-to-Many: User has many projects (as creator)
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }
    
    // Many-to-Many: User is member of many projects
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }
    
    // One-to-Many: User has many activity logs
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
    
    // One-to-Many: User has many sessions
    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }
}
```

**Foreign Keys:**
- `created_by` (in campaigns, tasks, projects, scheduled_posts)
- `assignee_id` (in tasks)
- `user_id` (in various tables)

---

### 2. Organization Model

**Table:** `organizations`

**Relationships:**

```php
class Organization extends Model
{
    // Many-to-Many: Organization has many users
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('role_id', 'created_at')
            ->withTimestamps();
    }
    
    // One-to-Many: Organization has many brands
    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }
    
    // One-to-Many: Organization has many campaigns
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
    
    // One-to-Many: Organization has many products
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    
    // One-to-Many: Organization has many channels
    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }
    
    // One-to-Many: Organization has many social connections
    public function socialConnections(): HasMany
    {
        return $this->hasMany(SocialConnection::class);
    }
    
    // One-to-Many: Organization has many email campaigns
    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class);
    }
    
    // One-to-Many: Organization has many contacts
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
    
    // One-to-Many: Organization has many contact lists
    public function contactLists(): HasMany
    {
        return $this->hasMany(ContactList::class);
    }
    
    // One-to-Many: Organization has many scheduled posts
    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }
    
    // One-to-Many: Organization has many tasks
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
    
    // One-to-Many: Organization has many projects
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
    
    // One-to-One: Organization has one subscription
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }
    
    // BelongsTo: Organization belongs to subscription plan
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
    
    // One-to-Many: Organization has many settings
    public function settings(): HasMany
    {
        return $this->hasMany(OrganizationSetting::class);
    }
    
    // Many-to-Many: Organization belongs to many agencies (through agency_clients)
    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(Agency::class, 'agency_clients')
            ->withPivot('status')
            ->withTimestamps();
    }
}
```

**Foreign Keys:**
- `organization_id` (in most tables)
- `subscription_plan_id` (in organizations table)

---

### 3. Campaign Model

**Table:** `campaigns`

**Relationships:**

```php
class Campaign extends Model
{
    // BelongsTo: Campaign belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Campaign created by user
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Many-to-Many: Campaign has many channels
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'campaign_channels')
            ->withPivot('budget', 'spent', 'status', 'created_at')
            ->withTimestamps();
    }
    
    // One-to-Many: Campaign has many goals
    public function goals(): HasMany
    {
        return $this->hasMany(CampaignGoal::class);
    }
    
    // One-to-Many: Campaign has many scheduled posts
    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }
    
    // One-to-Many: Campaign has many email campaigns
    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class);
    }
    
    // Many-to-Many: Campaign has many competitors
    public function competitors(): BelongsToMany
    {
        return $this->belongsToMany(Competitor::class, 'campaign_competitors')
            ->withTimestamps();
    }
}
```

**Foreign Keys:**
- `organization_id`
- `created_by`

**Pivot Tables:**
- `campaign_channels` (campaign_id, channel_id, budget, spent, status)

---

### 4. Brand Model

**Table:** `brands`

**Relationships:**

```php
class Brand extends Model
{
    // BelongsTo: Brand belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // One-to-Many: Brand has many products
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    
    // One-to-Many: Brand has many assets
    public function assets(): HasMany
    {
        return $this->hasMany(BrandAsset::class);
    }
    
    // One-to-Many: Brand has many name suggestions
    public function nameSuggestions(): HasMany
    {
        return $this->hasMany(BrandNameSuggestion::class);
    }
    
    // One-to-Many: Brand has many reviews
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    
    // One-to-One: Brand has one guideline
    public function guideline(): HasOne
    {
        return $this->hasOne(BrandGuideline::class);
    }
}
```

**Foreign Keys:**
- `organization_id`

---

### 5. Product Model

**Table:** `products`

**Relationships:**

```php
class Product extends Model
{
    // BelongsTo: Product belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Product belongs to brand
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    
    // BelongsTo: Product belongs to category
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category');
    }
    
    // One-to-Many: Product has many images
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
    
    // One-to-Many: Product has many variants
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
```

**Foreign Keys:**
- `organization_id`
- `brand_id`
- `category` (references product_categories.id)

---

### 6. Channel Model

**Table:** `channels`

**Relationships:**

```php
class Channel extends Model
{
    // BelongsTo: Channel belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // Many-to-Many: Channel belongs to many campaigns
    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_channels')
            ->withPivot('budget', 'spent', 'status', 'created_at')
            ->withTimestamps();
    }
    
    // One-to-Many: Channel has many scheduled posts
    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }
    
    // One-to-One: Channel has one settings
    public function settings(): HasOne
    {
        return $this->hasOne(ChannelSetting::class);
    }
    
    // Polymorphic: Channel can be different types
    public function channelable(): MorphTo
    {
        return $this->morphTo();
    }
}
```

**Foreign Keys:**
- `organization_id`

**Polymorphic Types:**
- `EmailChannel`
- `WhatsappChannel`
- `AmplifyChannel`
- `PaidAdChannel`
- `PrintMediaChannel`
- `InfluencerChannel`

---

### 7. ScheduledPost Model

**Table:** `scheduled_posts`

**Relationships:**

```php
class ScheduledPost extends Model
{
    // BelongsTo: Scheduled post belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Scheduled post belongs to campaign
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
    
    // BelongsTo: Scheduled post belongs to channel
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
    
    // BelongsTo: Scheduled post created by user
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // One-to-Many: Scheduled post has many content approvals
    public function approvals(): HasMany
    {
        return $this->hasMany(ContentApproval::class, 'content_id')
            ->where('content_type', ScheduledPost::class);
    }
    
    // One-to-Many: Scheduled post has many published posts
    public function publishedPosts(): HasMany
    {
        return $this->hasMany(PublishedPost::class);
    }
}
```

**Foreign Keys:**
- `organization_id`
- `campaign_id`
- `channel_id`
- `created_by`

---

### 8. EmailCampaign Model

**Table:** `email_campaigns`

**Relationships:**

```php
class EmailCampaign extends Model
{
    // BelongsTo: Email campaign belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Email campaign belongs to campaign
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
    
    // BelongsTo: Email campaign belongs to email template
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }
    
    // Many-to-Many: Email campaign has many contact lists
    public function contactLists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'email_campaign_contact_lists')
            ->withTimestamps();
    }
    
    // One-to-Many: Email campaign has many recipients (via CampaignRecipient intermediate model)
    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class, 'email_campaign_id');
    }
    
    // Many-to-Many: Email campaign has many contact lists
    public function contactLists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'email_campaign_contact_lists')
            ->withTimestamps();
    }
    
    // One-to-Many: Email campaign has many tracking events
    public function tracking(): HasMany
    {
        return $this->hasMany(EmailTracking::class);
    }
}
```

**Foreign Keys:**
- `organization_id`
- `campaign_id`
- `email_template_id`

**Intermediate Models:**
- `CampaignRecipient` (email_campaign_id, contact_id, status, sent_at, delivered_at, opened_at, clicked_at, open_count, click_count, error_message)

---

### 9. Contact Model

**Table:** `contacts`

**Relationships:**

```php
class Contact extends Model
{
    // BelongsTo: Contact belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // Many-to-Many: Contact belongs to many lists
    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'contact_list_contacts')
            ->withPivot('added_at')
            ->withTimestamps();
    }
    
    // One-to-Many: Contact has many tags (via ContactTag intermediate model)
    public function tags(): HasMany
    {
        return $this->hasMany(ContactTag::class);
    }
    
    // Many-to-Many: Contact receives many email campaigns (via CampaignRecipient intermediate model)
    public function emailCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(EmailCampaign::class, 'campaign_recipients')
            ->using(CampaignRecipient::class)
            ->withPivot('status', 'sent_at', 'delivered_at', 'opened_at', 'clicked_at', 'open_count', 'click_count', 'error_message')
            ->withTimestamps();
    }
    
    // One-to-Many: Contact has many activities
    public function activities(): HasMany
    {
        return $this->hasMany(ContactActivity::class);
    }
}
```

**Foreign Keys:**
- `organization_id`

**Pivot Tables:**
- `contact_list_contacts` (contact_id, list_id)

**Intermediate Models:**
- `ContactTag` (contact_id, tag) - stores tags as strings
- `CampaignRecipient` (email_campaign_id, contact_id, status, sent_at, delivered_at, opened_at, clicked_at, open_count, click_count, error_message)

---

### 10. Agency Model

**Table:** `agencies`

**Relationships:**

```php
class Agency extends Model
{
    // BelongsTo: Agency owned by user
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    
    // Many-to-Many: Agency has many clients (organizations)
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'agency_clients')
            ->withPivot('status', 'created_at')
            ->withTimestamps();
    }
    
    // Many-to-Many: Agency has many team members
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'agency_team_members')
            ->withPivot('role', 'created_at')
            ->withTimestamps();
    }
    
    // Has Many Through: Agency has many tasks through clients
    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Organization::class, 'agency_id', 'organization_id');
    }
}
```

**Foreign Keys:**
- `owner_id` (references users.id)

**Pivot Tables:**
- `agency_clients` (agency_id, organization_id, status)
- `agency_team_members` (agency_id, user_id, role)

---

### 11. Task Model

**Table:** `tasks`

**Relationships:**

```php
class Task extends Model
{
    // BelongsTo: Task belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Task belongs to agency (optional)
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
    
    // BelongsTo: Task assigned to user
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
    
    // BelongsTo: Task created by user
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // One-to-Many: Task has many comments
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }
    
    // One-to-Many: Task has many attachments
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }
}
```

**Foreign Keys:**
- `organization_id`
- `agency_id` (nullable)
- `assignee_id`
- `created_by`

---

### 12. Project Model

**Table:** `projects`

**Relationships:**

```php
class Project extends Model
{
    // BelongsTo: Project belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Project created by user
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Many-to-Many: Project has many members
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'created_at')
            ->withTimestamps();
    }
    
    // BelongsTo: Project has workflow state
    public function workflowState(): BelongsTo
    {
        return $this->belongsTo(WorkflowState::class, 'status');
    }
}
```

**Foreign Keys:**
- `organization_id`
- `created_by`
- `status` (references workflow_states.id)

**Pivot Tables:**
- `project_members` (project_id, user_id, role)

---

### 13. ChatTopic Model

**Table:** `chat_topics`

**Relationships:**

```php
class ChatTopic extends Model
{
    // BelongsTo: Chat topic belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Chat topic created by user
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // One-to-Many: Chat topic has many messages
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
    
    // Many-to-Many: Chat topic has many participants
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants')
            ->withPivot('last_read_at', 'joined_at')
            ->withTimestamps();
    }
}
```

**Foreign Keys:**
- `organization_id`
- `created_by`

**Pivot Tables:**
- `chat_participants` (topic_id, user_id, last_read_at, joined_at)

---

### 14. Workflow Model

**Table:** `workflows`

**Relationships:**

```php
class Workflow extends Model
{
    // BelongsTo: Workflow belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Workflow created by user
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // One-to-Many: Workflow has many triggers
    public function triggers(): HasMany
    {
        return $this->hasMany(WorkflowTrigger::class);
    }
    
    // One-to-Many: Workflow has many actions
    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }
    
    // One-to-Many: Workflow has many executions
    public function executions(): HasMany
    {
        return $this->hasMany(WorkflowExecution::class);
    }
}
```

**Foreign Keys:**
- `organization_id`
- `created_by`

---

### 15. Subscription Model

**Table:** `subscriptions`

**Relationships:**

```php
class Subscription extends Model
{
    // BelongsTo: Subscription belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Subscription belongs to plan
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }
    
    // One-to-Many: Subscription has many invoices
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
```

**Foreign Keys:**
- `organization_id`
- `plan_id`

---

### 16. Invoice Model

**Table:** `invoices`

**Relationships:**

```php
class Invoice extends Model
{
    // BelongsTo: Invoice belongs to organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // BelongsTo: Invoice belongs to subscription
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
    
    // One-to-Many: Invoice has many items
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
    // One-to-Many: Invoice has many payments
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
```

**Foreign Keys:**
- `organization_id`
- `subscription_id`

---

## Relationship Diagrams

### High-Level Entity Relationship

```
User
├── belongsToMany → Organization (via user_roles)
├── belongsToMany → Role (via user_roles)
├── belongsToMany → Agency (via agency_team_members)
├── hasMany → Campaign (created_by)
├── hasMany → Task (assignee_id, created_by)
├── hasMany → Project (created_by)
└── belongsToMany → Project (via project_members)

Organization
├── belongsToMany → User (via user_roles)
├── belongsToMany → Agency (via agency_clients)
├── hasMany → Brand
├── hasMany → Campaign
├── hasMany → Product
├── hasMany → Channel
├── hasMany → Task
├── hasMany → Project
├── hasOne → Subscription
└── hasMany → Invoice

Campaign
├── belongsTo → Organization
├── belongsTo → User (created_by)
├── belongsToMany → Channel (via campaign_channels)
├── hasMany → ScheduledPost
├── hasMany → EmailCampaign
└── hasMany → CampaignGoal

Brand
├── belongsTo → Organization
├── hasMany → Product
├── hasMany → BrandAsset
└── hasMany → Review

Agency
├── belongsTo → User (owner_id)
├── belongsToMany → Organization (via agency_clients)
└── belongsToMany → User (via agency_team_members)
```

### Many-to-Many Relationships

**Pivot Tables:**

1. **user_roles** (user_id, role_id, organization_id, agency_id)
   - Links users to roles within organizations/agencies

2. **campaign_channels** (campaign_id, channel_id, budget, spent, status)
   - Links campaigns to channels with additional data

3. **contact_list_contacts** (contact_id, list_id)
   - Links contacts to contact lists

4. **campaign_recipients** (campaign_id, contact_id, status, opened_at, clicked_at)
   - Links email campaigns to recipients with tracking data

5. **agency_clients** (agency_id, organization_id, status)
   - Links agencies to client organizations

6. **agency_team_members** (agency_id, user_id, role)
   - Links agencies to team members

7. **project_members** (project_id, user_id, role)
   - Links projects to team members

8. **chat_participants** (topic_id, user_id, last_read_at, joined_at)
   - Links chat topics to participants

---

## Query Examples

### Eager Loading Examples

```php
// Load organization with all related data
$organization = Organization::with([
    'users',
    'brands',
    'campaigns.channels',
    'products.brand',
    'tasks.assignee',
    'projects.members'
])->find($id);

// Load campaign with all relationships
$campaign = Campaign::with([
    'organization',
    'creator',
    'channels',
    'goals',
    'scheduledPosts.channel',
    'emailCampaigns.recipients'
])->find($id);

// Load user with organizations and roles
$user = User::with([
    'organizations',
    'roles',
    'agencies',
    'assignedTasks.organization',
    'createdCampaigns.organization'
])->find($id);

// Load task with all related data
$task = Task::with([
    'organization',
    'assignee',
    'creator',
    'comments.user',
    'attachments'
])->find($id);
```

### Relationship Query Examples

```php
// Get all campaigns for an organization
$campaigns = Organization::find($id)->campaigns;

// Get all users in an organization
$users = Organization::find($id)->users;

// Get all tasks assigned to a user
$tasks = User::find($id)->assignedTasks;

// Get all products for a brand
$products = Brand::find($id)->products;

// Get all scheduled posts for a campaign
$posts = Campaign::find($id)->scheduledPosts;

// Get all email campaigns for an organization
$emailCampaigns = Organization::find($id)->emailCampaigns;

// Get all contacts in a list
$contacts = ContactList::find($id)->contacts;

// Get all members of a project
$members = Project::find($id)->members;

// Get all participants in a chat topic
$participants = ChatTopic::find($id)->participants;
```

### Complex Relationship Queries

```php
// Get all campaigns with their channels and scheduled posts
$campaigns = Campaign::with(['channels', 'scheduledPosts'])
    ->where('organization_id', $organizationId)
    ->get();

// Get all tasks for an agency across all clients
$tasks = Task::whereHas('organization', function ($query) use ($agencyId) {
    $query->whereHas('agencies', function ($q) use ($agencyId) {
        $q->where('agencies.id', $agencyId);
    });
})->get();

// Get all users with their roles in a specific organization
$users = User::whereHas('organizations', function ($query) use ($orgId) {
    $query->where('organizations.id', $orgId);
})->with(['roles' => function ($query) use ($orgId) {
    $query->wherePivot('organization_id', $orgId);
}])->get();

// Get all scheduled posts for channels in a campaign
$posts = ScheduledPost::whereHas('campaign', function ($query) use ($campaignId) {
    $query->where('id', $campaignId);
})->with('channel')->get();
```

---

## Eager Loading Strategies

### Nested Eager Loading

```php
// Load organization with nested relationships
$organization = Organization::with([
    'campaigns' => function ($query) {
        $query->with(['channels', 'scheduledPosts']);
    },
    'brands' => function ($query) {
        $query->with(['products', 'assets']);
    },
    'tasks' => function ($query) {
        $query->with(['assignee', 'comments']);
    }
])->find($id);
```

### Conditional Eager Loading

```php
// Load only active campaigns
$organization = Organization::with([
    'campaigns' => function ($query) {
        $query->where('status', 'Active');
    }
])->find($id);

// Load only published scheduled posts
$campaign = Campaign::with([
    'scheduledPosts' => function ($query) {
        $query->where('status', 'Published');
    }
])->find($id);
```

### Lazy Eager Loading

```php
// Load relationships after the fact
$organization = Organization::find($id);
$organization->load(['campaigns', 'brands', 'products']);

// Load missing relationships
if (!$organization->relationLoaded('campaigns')) {
    $organization->load('campaigns');
}
```

---

## Relationship Best Practices

### 1. Always Use Eager Loading
```php
// ❌ BAD: N+1 query problem
$campaigns = Campaign::all();
foreach ($campaigns as $campaign) {
    echo $campaign->organization->name; // Query for each campaign
}

// ✅ GOOD: Eager loading
$campaigns = Campaign::with('organization')->get();
foreach ($campaigns as $campaign) {
    echo $campaign->organization->name; // No additional queries
}
```

### 2. Use Relationship Methods
```php
// ❌ BAD: Manual query
$campaigns = Campaign::where('organization_id', $orgId)->get();

// ✅ GOOD: Use relationship
$campaigns = $organization->campaigns;
```

### 3. Filter Relationships
```php
// ✅ GOOD: Filtered relationship
$activeCampaigns = $organization->campaigns()
    ->where('status', 'Active')
    ->get();
```

### 4. Use Pivot Data
```php
// ✅ GOOD: Access pivot data
$campaign = Campaign::find($id);
foreach ($campaign->channels as $channel) {
    echo $channel->pivot->budget; // Access pivot data
    echo $channel->pivot->spent;
}
```

---

## Summary

This document provides a comprehensive reference for all model relationships in the MarketPulse application. Key points:

1. **114+ database tables** with relationships documented
2. **Multiple relationship types** (hasMany, belongsTo, belongsToMany, etc.)
3. **Pivot tables** for many-to-many relationships
4. **Query examples** for common use cases
5. **Eager loading strategies** for performance optimization

Use this document as a reference when:
- Writing queries
- Setting up relationships
- Optimizing database queries
- Understanding data flow
- Debugging relationship issues

---

*Document Version: 1.0*  
*Last Updated: [Current Date]*  
*Prepared for: MarketPulse Laravel 12 Redevelopment*


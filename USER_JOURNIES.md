# MarketPulse - User Journey Documentation

This document provides comprehensive user journey maps for all user types in the MarketPulse application, detailing step-by-step workflows, decision points, and key interactions.

## Document Structure

For each user journey, you'll find:
1. **Journey Overview** - High-level description and goals
2. **Entry Points** - How users enter the journey
3. **Step-by-Step Flow** - Detailed workflow with decision points
4. **Key Interactions** - Important UI elements and actions
5. **Exit Points** - Where users transition to other journeys
6. **Common Variations** - Alternative paths and edge cases

---

## Table of Contents

1. [New User Onboarding Journey](#1-new-user-onboarding-journey)
2. [Organization Admin Daily Journey](#2-organization-admin-daily-journey)
3. [Client Role User Journey](#3-client-role-user-journey)
4. [Agency User Journey](#4-agency-user-journey)
5. [Platform Admin Journey](#5-platform-admin-journey)
6. [Campaign Creation Journey](#6-campaign-creation-journey)
7. [Content Review Journey](#7-content-review-journey)

---

## 1. New User Onboarding Journey

### Journey Overview
**User Type:** New Customer/Organization User  
**Goal:** Complete initial setup and create first brand  
**Duration:** 10-15 minutes  
**Success Criteria:** Organization created, first brand configured, ready to create campaigns

### Entry Points
- User visits `/signup` page
- User clicks "Sign Up" from landing page
- User redirected from email invitation

### Step-by-Step Flow

#### Step 1: Account Registration
**Route:** `/signup`  
**Actions:**
1. User enters:
   - Full name
   - Email address
   - Password (with validation)
2. User clicks "Create Account"
3. System creates user account
4. User receives success toast notification
5. **Auto-redirect:** `/main/onboarding`

**Key UI Elements:**
- Signup form with validation
- Password strength indicator
- Link to login page ("Already have an account?")
- MarketPulse branding

**Decision Points:**
- If email already exists → Show error, allow login redirect
- If validation fails → Show inline errors
- If signup succeeds → Proceed to onboarding

---

#### Step 2: AI-Powered Onboarding Wizard
**Route:** `/main/onboarding`  
**Duration:** 5-10 minutes  
**AI Assistant:** Jenna (AI marketing partner)

**Conversation Flow:**

**Question 1: Organization Name**
- AI: "Welcome to MarketPulse! I'm Jenna, your AI marketing partner. I'm here to help you get set up. First, what is the name of your organization?"
- User enters organization name
- System saves to `confirmedData.name`
- **Sidebar Update:** Organization name card appears

**Question 2: Website URL**
- AI: "Great! What is your company's website URL?"
- User can:
  - Enter full URL (validated)
  - Type "skip" to skip this step
- System fetches website content using `getWebsiteContent` tool
- **Sidebar Update:** Website card appears

**Question 3: Business Focus Detection**
- AI uses `determineFocusPrompt` to analyze website content
- AI: "It looks like you're a [detected focus]. Does that sound right?"
- User confirms or corrects
- System saves to `confirmedData.focus`
- **Sidebar Update:** Focus added to website card

**Question 4: Business Model**
- AI: "What's your business model? You can choose B2B, B2C, or D2C, or 'skip'."
- User selects:
  - B2B (Business-to-Business)
  - B2C (Business-to-Consumer)
  - D2C (Direct-to-Consumer)
  - "skip"
- **UI:** Button selector appears for quick selection
- System saves to `confirmedData.businessModel`
- **Sidebar Update:** Business model card appears

**Question 5: Completion**
- AI calls `setOrganizationInfo` tool
- AI: "Perfect! Your organization setup is complete. Let's head over to the Brands section to create your first brand."
- **Auto-redirect:** `/main/[organizationId]/brands`
- Success toast with confetti animation

**Key UI Elements:**
- Chat interface with message history
- AI avatar (Jenna)
- User avatar
- Sidebar showing confirmed data cards
- Edit buttons on cards (hover to reveal)
- "Skip for now" button (enabled after name entered)
- "Restart" button with confirmation dialog
- Auto-saving to localStorage (`onboarding_draft`)

**Decision Points:**
- User can skip any question → AI acknowledges and moves on
- User can edit confirmed data → Card becomes editable, conversation resumes
- User can restart → Confirmation dialog, clears all progress
- User can skip entire onboarding → Redirects to collaboration hub

**Data Persistence:**
- Auto-saves progress to localStorage
- Can resume if page refreshed
- Draft cleared on completion

---

#### Step 3: Create First Brand
**Route:** `/main/[organizationId]/brands`  
**Duration:** 5-10 minutes

**Actions:**
1. User sees brand list (empty initially)
2. User clicks "Add New Brand" button
3. Brand wizard/form opens
4. User enters:
   - Brand name
   - Brand summary
   - Brand guidelines (detailed)
   - Tone of voice settings
   - Target audience information
   - Keywords to use/avoid
5. User can optionally:
   - Enable "AI-Generated Concept Brand" toggle
   - Use visual brand ideation tool
   - Generate brand assets
6. User saves brand
7. Brand appears in brand list
8. **Brand Switcher Update:** Brand appears in dropdown

**Key UI Elements:**
- Brand list/grid view
- "Add New Brand" button
- Brand wizard/form
- Brand guidelines editor
- Visual ideation tools
- Brand asset upload

**Decision Points:**
- User can create multiple brands → Each added to list
- User can edit brand → Opens edit form
- User can delete brand → Confirmation required
- User can skip brand creation → Can proceed without brand (limited features)

**Next Steps:**
- User can select brand from switcher → Brand context activated
- User can proceed to create channels → Required for campaigns
- User can start creating campaigns → Full workflow available

---

### Exit Points
- **To Campaign Creation:** User selects brand, navigates to campaigns
- **To Channel Setup:** User navigates to channels page
- **To Collaboration Hub:** User clicks "Skip for now" or completes onboarding

### Common Variations

**Variation 1: Returning User (Rerun)**
- User visits `/main/onboarding?rerun=true`
- AI: "Welcome back to the Setup Wizard! Let's review and update your settings. First, your organization is named '[name]'. Is that still correct, or would you like to change it?"
- Pre-filled data from previous session
- User can update any field

**Variation 2: Agency-Onboarded User**
- User invited by agency
- Organization already exists
- User skips organization creation
- Goes directly to brand setup or collaboration hub

**Variation 3: Multi-Organization User**
- User belongs to multiple organizations
- After onboarding, redirected to organization selection
- Can switch between organizations

---

## 2. Organization Admin Daily Journey

### Journey Overview
**User Type:** Organization Admin (Full Access)  
**Goal:** Manage marketing campaigns, content, and team collaboration  
**Duration:** Ongoing (daily workflow)  
**Success Criteria:** Campaigns created, content approved, team coordinated

### Entry Points
- User logs in → Redirected to organization selection or default organization
- User selects organization from switcher
- User bookmarks organization dashboard

### Step-by-Step Flow

#### Morning Routine: Review & Planning

**Step 1: Access Collaboration Hub**
**Route:** `/main/[organizationId]/collaboration`  
**Default View:** Wall Feed

**Actions:**
1. User sees welcome card with instructions
2. User reviews "For Your Review" section:
   - Up to 3 pending content items
   - Quick approve/reject actions
   - Link to full review page
3. User checks recent activity feed:
   - Recent approvals
   - Team additions
   - Calendar events
   - Campaign creations
4. User can:
   - Click topic from sidebar → Opens chat panel
   - Start new topic → Creates hashtag conversation
   - Send direct message → Creates DM conversation

**Key UI Elements:**
- Wall feed with activity cards
- Review items with quick actions
- Topic list sidebar (left)
- Chat panel (when topic selected)
- Unread badges on topics
- Mentions indicator (red badge)

**Decision Points:**
- If review items exist → User can approve/reject immediately
- If no review items → User proceeds to other tasks
- If mentions exist → User clicks to view notifications

---

**Step 2: Review Pending Content**
**Route:** `/main/[organizationId]/review?brandId=[brandId]`  
**Prerequisite:** Brand must be selected

**Actions:**
1. User sees review table with all pending items
2. User filters by:
   - Status (Pending Review, Approved, Rejected)
   - Channel (platform badges)
   - Author
   - Submission date
3. User clicks "View" on content item
4. Review dialog opens showing:
   - Full content display
   - Attachment preview (images/PDFs)
   - PDF annotation detection
   - Approve/Reject buttons
   - Comment field
5. User:
   - Reviews content
   - Adds comments (optional)
   - Clicks "Approve" or "Reject"
6. System:
   - Updates status
   - Sends notification to author
   - Updates review count badge
   - Removes from pending list

**Key UI Elements:**
- Review table with filters
- Status badges (Pending/Approved/Rejected)
- Review dialog with full content
- PDF annotation viewer
- Approve/Reject buttons
- Comment input

**Decision Points:**
- If content approved → Moves to approved status, ready for publishing
- If content rejected → Returns to author with comments
- If PDF annotations detected → User can review annotations

---

#### Campaign Creation Workflow

**Step 3: Create New Campaign**
**Route:** `/main/[organizationId]/campaigns/create`  
**Prerequisite:** Brand selected (recommended)

**Multi-Step Process:**

**Step 3a: Plan Campaign**
- User fills campaign plan form:
  - Campaign goal (text input)
  - Goal type selector:
    - Product Launch
    - Brand Awareness
    - Lead Generation
    - Event Promotion
    - Custom
  - Product selection (if applicable)
  - Brand selection
  - Goal prompt/URL (optional)
- User can:
  - Get AI suggestions → Click "Get Suggestions"
  - Use AI suggestion → Click suggestion card
  - Enter custom goal
- User selects channels:
  - Facebook
  - Instagram
  - LinkedIn
  - Twitter/X
  - Pinterest
  - WhatsApp
  - Email
  - Influencer (opens dialog)
- User clicks "Generate Campaign Plan"
- AI generates:
  - Campaign strategy
  - Content themes
  - Posting schedule
  - Channel-specific recommendations
- User reviews plan
- User clicks "Continue to Content Generation"

**Key UI Elements:**
- Campaign plan form
- Goal type selector
- Product/Brand dropdowns
- Channel checkboxes with icons
- AI suggestion cards
- "Generate Plan" button
- Campaign stepper (shows "Plan" step active)

**Decision Points:**
- If no channels selected → Validation error
- If plan generation fails → Error message, retry option
- User can save draft → Stored in localStorage

---

**Step 3b: Generate Content**
**Route:** `/main/[organizationId]/campaigns/content?campaignId=[id]`

**Actions:**
1. User sees campaign plan summary
2. User clicks "Generate Initial Launch Content"
3. AI generates content for each channel:
   - Post copy
   - Image suggestions
   - Hashtags
   - Posting times
4. User reviews generated content:
   - Channel-specific previews
   - Content variations
   - Scheduling information
5. User can:
   - Edit individual posts
   - Regenerate content
   - Add more posts
   - Adjust schedule
6. User clicks "Submit for Review"
7. System:
   - Creates scheduled posts
   - Sets campaign status to "In Review"
   - Adds content to review queue
   - Sends notifications to reviewers

**Key UI Elements:**
- Content generation form
- Channel content previews
- Edit buttons on each post
- Regenerate button
- Schedule editor
- "Submit for Review" button
- Campaign stepper (shows "Content" step active)

**Decision Points:**
- If content needs editing → User edits before submission
- If content approved → Campaign moves to "Active" status
- If content rejected → Returns to content generation step

---

**Step 3c: Review & Publish**
**Route:** `/main/[organizationId]/campaigns`  
**Status:** Campaign in "In Review" or "Active"

**Actions:**
1. User views campaign in campaigns list
2. User sees status badge:
   - Draft (gray)
   - In Review (yellow)
   - Active (green)
   - Completed (blue)
   - Inactive (gray)
3. For "In Review" campaigns:
   - User clicks "Go to Review"
   - Reviews all content
   - Approves/rejects items
4. For "Active" campaigns:
   - User can:
     - View scheduled posts
     - Publish all now
     - Edit schedule
     - Deactivate campaign
5. System publishes posts according to schedule

**Key UI Elements:**
- Campaign list table
- Status badges
- Action menu (three dots)
- "Publish All Now" button
- Schedule calendar view

**Decision Points:**
- If all content approved → Campaign becomes "Active"
- If some content rejected → Campaign stays "In Review"
- User can publish immediately → Bypasses schedule

---

#### Afternoon: Content Management

**Step 4: Manage Channels**
**Route:** `/main/[organizationId]/channels`  
**Prerequisite:** No brand selected

**Actions:**
1. User views channel list
2. User can:
   - Add new channel → Opens channel form
   - Edit existing channel → Opens edit form
   - Test connection → Verifies API keys
   - Delete channel → Confirmation required
3. For each channel, user configures:
   - Platform type
   - API keys/credentials
   - Default settings
   - Posting preferences

**Key UI Elements:**
- Channel list/grid
- "Add Channel" button
- Channel cards with status indicators
- Connection test button
- Settings icon

---

**Step 5: Manage Products**
**Route:** `/main/[organizationId]/products`  
**Prerequisite:** No brand selected

**Actions:**
1. User views product catalog
2. User can:
   - Add new product → Opens product form
   - Import products → CSV/Excel import
   - Edit product → Opens edit form
   - Delete product → Confirmation required
   - Filter by category → Category sidebar
3. Product form includes:
   - Name, description, price
   - Images upload
   - Category assignment
   - SKU and inventory
   - Product variants

**Key UI Elements:**
- Product grid/list
- Category sidebar
- "Add Product" button
- "Import Products" button
- Product cards with images

---

**Step 6: Team Collaboration**
**Route:** `/main/[organizationId]/collaboration`

**Actions:**
1. User accesses collaboration hub
2. User creates/joins topics:
   - Hashtag conversations (#campaign-name)
   - Direct messages (DM)
3. User sends messages:
   - Text messages
   - File attachments
   - Mentions (@username)
4. User receives notifications:
   - New messages in topics
   - Mentions
   - Review requests
   - Task assignments

**Key UI Elements:**
- Topic list sidebar
- Chat panel
- Message input
- File upload
- Mention autocomplete
- Notification bell with badge

---

#### Evening: Analytics & Reporting

**Step 7: View Analytics**
**Route:** `/main/[organizationId]/analytics?brandId=[brandId]`  
**Prerequisite:** Brand selected

**Actions:**
1. User selects campaign from dropdown
2. User enters client name (optional)
3. User clicks "Analyze"
4. AI processes campaign data:
   - Platform metrics (impressions, clicks, conversions)
   - Performance trends
   - Engagement rates
5. User views analysis:
   - Executive summary
   - Key insights (bullet points)
   - Recommendations (actionable suggestions)
   - Metrics grid with changes

**Key UI Elements:**
- Campaign selector dropdown
- Client name input
- "Analyze" button
- Analysis results panel
- Metrics grid
- Insights list
- Recommendations list

---

**Step 8: Manage Organization Settings**
**Route:** `/main/[organizationId]/settings`  
**Prerequisite:** Organization Admin role

**Actions:**
1. User accesses settings page
2. User manages:
   - General settings
   - Team members (add/remove, assign roles)
   - Billing (subscription, payment methods)
   - Storage sources (cloud integrations)
   - Automations (workflow builder)
   - Integrations (third-party services)
3. User saves changes

**Key UI Elements:**
- Settings tabs
- Team member list
- "Add Team Member" button
- Role selector
- Billing information
- Integration cards

---

### Exit Points
- **To Agency View:** User switches to agency context (if member)
- **To Admin Panel:** User with admin role accesses `/admin/*`
- **Logout:** User ends session

### Common Variations

**Variation 1: Quick Campaign Creation**
- User uses AI campaign suggestion
- Pre-filled form
- Faster workflow

**Variation 2: Bulk Content Generation**
- User generates content for multiple campaigns
- Batch processing
- Review all at once

**Variation 3: Team Delegation**
- Admin assigns tasks to team members
- Team members create content
- Admin reviews and approves

---

## 3. Client Role User Journey

### Journey Overview
**User Type:** Client Role (Limited Access)  
**Goal:** Review content, collaborate with team, view analytics  
**Duration:** As needed (review-focused)  
**Success Criteria:** Content reviewed and approved, team informed

### Entry Points
- User logs in → Redirected to organization
- User invited as Client role
- User accesses review notifications

### Step-by-Step Flow

#### Primary Workflow: Content Review

**Step 1: Access Collaboration Hub**
**Route:** `/main/[organizationId]/collaboration`

**Actions:**
1. User sees collaboration hub (same as Admin)
2. User focuses on "For Your Review" section
3. User sees pending review items
4. User clicks review item or "View All"

**Key Differences from Admin:**
- Cannot create campaigns
- Cannot access organization settings
- Limited to review and collaboration features

---

**Step 2: Review Content**
**Route:** `/main/[organizationId]/review?brandId=[brandId]`

**Actions:**
1. User views review table
2. User filters by status, channel, author
3. User clicks "View" on content item
4. Review dialog opens
5. User:
   - Reads content carefully
   - Checks attachments
   - Reviews PDF annotations (if any)
   - Adds comments (optional)
   - Clicks "Approve" or "Reject"
6. System updates status and notifies author

**Key UI Elements:**
- Same review interface as Admin
- Approve/Reject buttons
- Comment field
- PDF viewer

**Decision Points:**
- If content approved → Moves to approved status
- If content rejected → Returns to author with feedback
- User can request changes → Adds comments, rejects

---

**Step 3: View Brand Assets**
**Route:** `/main/[organizationId]/brand-assets?brandId=[brandId]`

**Actions:**
1. User views brand assets (read-only)
2. User can:
   - Browse brand guidelines
   - View logos and colors
   - Download assets (if permitted)
   - View brand information

**Key UI Elements:**
- Brand asset gallery
- Brand guidelines viewer
- Download buttons (if permitted)

---

**Step 4: View Analytics**
**Route:** `/main/[organizationId]/analytics?brandId=[brandId]`

**Actions:**
1. User views analytics dashboard (read-only)
2. User can:
   - View campaign performance
   - See metrics and insights
   - Export reports (if permitted)
   - Cannot modify settings

**Key UI Elements:**
- Analytics dashboard
- Metrics charts
- Export button (if permitted)

---

**Step 5: Collaborate via Chat**
**Route:** `/main/[organizationId]/collaboration`

**Actions:**
1. User accesses topics/DMs
2. User sends messages
3. User receives notifications
4. User can mention team members

**Key UI Elements:**
- Same chat interface as Admin
- Topic list
- Chat panel

---

### Exit Points
- **To Review:** User checks review queue regularly
- **To Collaboration:** User participates in team discussions
- **Logout:** User ends session

### Common Variations

**Variation 1: Bulk Review**
- User reviews multiple items in sequence
- Faster approval workflow
- Batch actions (if implemented)

**Variation 2: Request Changes**
- User rejects content with detailed comments
- Author revises and resubmits
- User reviews again

---

## 4. Agency User Journey

### Journey Overview
**User Type:** Agency Member or Agency Admin  
**Goal:** Manage multiple client organizations, coordinate work, generate reports  
**Duration:** Ongoing (client management)  
**Success Criteria:** Clients managed, tasks completed, reports delivered

### Entry Points
- User logs in → Selects agency from organization switcher
- User accesses `/agency/[agencyId]/*` directly
- User switches from organization view to agency view

### Step-by-Step Flow

#### Agency Member Journey

**Step 1: View Clients**
**Route:** `/agency/[agencyId]/clients`  
**Default Route:** Redirects from `/agency/[agencyId]`

**Actions:**
1. User sees client organization list
2. Each client shows:
   - Organization name
   - Total users count
   - "View Organization" button
3. User clicks "View Organization"
4. System opens client's organization view in new context
5. User can work within client's organization

**Key UI Elements:**
- Client table/list
- User count badges
- "View Organization" buttons
- "Add New Client" button (if admin)

**Decision Points:**
- User can access any assigned client → Opens client organization
- User can switch between clients → Returns to clients list

---

**Step 2: Manage Cross-Client Tasks**
**Route:** `/agency/[agencyId]/tasks`

**Actions:**
1. User sees unified Kanban board
2. Tasks from all clients displayed:
   - To Do column
   - In Progress column
   - Done column
3. Each task card shows:
   - Client badge (identifies which client)
   - Task title
   - Due date
   - Assignee avatar
4. User can:
   - Drag tasks between columns
   - Click task to view details
   - Create new tasks for any client
   - Assign to agency team members
5. Task details show:
   - Client name
   - Full description
   - Discussion panel
   - Attachments

**Key UI Elements:**
- Kanban board (three columns)
- Task cards with client badges
- Drag-and-drop interface
- Task detail dialog
- "Create Task" button

**Decision Points:**
- User can filter by client → Shows only that client's tasks
- User can create task for specific client → Selects client in form

---

**Step 3: View Aggregated Calendar**
**Route:** `/agency/[agencyId]/calendar`

**Actions:**
1. User sees full calendar view
2. All client events displayed:
   - Scheduled posts from all clients
   - Campaign launches
   - Content publication dates
3. Events color-coded by client
4. User can:
   - Click event for details
   - Navigate between months/weeks
   - Filter by client (potential feature)
   - View day/week/month views

**Key UI Elements:**
- Full calendar component
- Color-coded events
- Event detail popup
- Calendar navigation
- View selector (day/week/month)

---

**Step 4: Generate Client Reports**
**Route:** `/agency/[agencyId]/reports`

**Actions:**
1. User selects client from dropdown
2. User selects report type:
   - Weekly
   - Monthly
   - Quarterly
3. User clicks "Generate Report"
4. AI processes client data:
   - Campaign performance
   - Content metrics
   - Engagement data
5. User views generated report:
   - Executive summary
   - Key metrics with changes
   - Highlights (positive achievements)
   - Recommendations (improvement suggestions)
6. User can:
   - Download as PDF
   - Share with client
   - Schedule automatic reports

**Key UI Elements:**
- Client selector dropdown
- Report type selector
- "Generate Report" button
- Report display panel
- Metrics grid
- "Download PDF" button

**Decision Points:**
- User can generate multiple reports → Each processed separately
- User can schedule reports → Automated generation

---

#### Agency Admin Journey

**Step 5: Manage Billing & Invoicing**
**Route:** `/agency/[agencyId]/billing`  
**Prerequisite:** Agency Admin role

**Actions:**
1. User views billing dashboard
2. Summary cards show:
   - Total Billed (This Year)
   - Pending Payments
   - Overdue Payments
3. User views invoice table:
   - Invoice ID
   - Organization name (client)
   - Amount
   - Status (Paid/Pending/Overdue)
   - Issue date
   - Due date
4. User can:
   - Download invoice PDF
   - Mark invoice as paid
   - Toggle automated reminders
   - Run reminder check manually
5. Automation card:
   - Toggle for automated reminders
   - Manual reminder check button

**Key UI Elements:**
- Summary cards
- Invoice table
- Status badges (green/yellow/red)
- "Download" button
- "Pay Now" button
- Automation toggle

**Decision Points:**
- If invoice paid → Status updates, removed from pending
- If overdue → Highlighted, reminders sent
- User can enable automation → Automatic reminder system

---

**Step 6: Manage Agency Team**
**Route:** `/agency/[agencyId]/team`  
**Prerequisite:** Agency Admin role

**Actions:**
1. User views team member list
2. User can:
   - Add team members → Invite users
   - Remove team members → Remove access
   - Assign roles → Agency Member or Agency Admin
   - Assign client access → Control which clients each member can access
3. User manages permissions:
   - Client access permissions
   - Feature access
   - Reporting permissions

**Key UI Elements:**
- Team member list
- "Add Team Member" button
- Role selector
- Client access checkboxes
- Permission settings

---

**Step 7: Configure Agency Settings**
**Route:** `/agency/[agencyId]/settings`  
**Prerequisite:** Agency Admin role

**Actions:**
1. User accesses agency settings
2. User configures:
   - Agency profile (name, description)
   - Branding (logo, colors)
   - Default settings
   - Integrations
   - Notification preferences
3. User saves changes

**Key UI Elements:**
- Settings form
- Profile fields
- Branding upload
- Integration cards

---

### Exit Points
- **To Client Organization:** User clicks "View Organization" → Opens client view
- **To Organization View:** User exits agency view → Returns to organization selection
- **Logout:** User ends session

### Common Variations

**Variation 1: Client-Specific Work**
- User focuses on single client
- Accesses client's organization directly
- Works within client context

**Variation 2: Multi-Client Coordination**
- User manages tasks across multiple clients
- Uses aggregated calendar
- Generates reports for all clients

**Variation 3: Team Collaboration**
- Agency team members collaborate
- Tasks assigned across team
- Shared calendar visibility

---

## 5. Platform Admin Journey

### Journey Overview
**User Type:** Platform Administrator (Super Admin)  
**Goal:** Manage platform, organizations, users, and system settings  
**Duration:** Ongoing (system administration)  
**Success Criteria:** Platform stable, organizations managed, users supported

### Entry Points
- User with `user_type = 'admin'` logs in → Redirected to `/admin/dashboard`
- User accesses `/admin/*` routes directly
- User switches from organization view to admin panel

### Step-by-Step Flow

#### Daily Administration

**Step 1: View Dashboard**
**Route:** `/admin/dashboard`

**Actions:**
1. User sees system overview:
   - Key metrics (total organizations, users, campaigns)
   - Platform health indicators
   - Recent activity
   - System alerts
2. User monitors:
   - Active organizations
   - User growth
   - System performance
   - Error rates

**Key UI Elements:**
- Dashboard cards with metrics
- Charts and graphs
- Activity feed
- Alert notifications
- Quick action buttons

---

**Step 2: Manage Organizations**
**Route:** `/admin/organizations`

**Actions:**
1. User views all organizations
2. User can:
   - View organization details
   - Edit organization settings
   - Change subscription plans
   - Suspend/activate organizations
   - Delete organizations (with caution)
3. User sees:
   - Organization name
   - Subscription status
   - User count
   - Created date
   - Last activity

**Key UI Elements:**
- Organization table
- Status badges
- Action menu
- "View Details" button
- Subscription info

**Decision Points:**
- If organization suspended → Access restricted
- If subscription expired → Notifications sent
- User can access any organization's data → Full platform access

---

**Step 3: Manage Users**
**Route:** `/admin/users`

**Actions:**
1. User views all platform users
2. User can:
   - View user details
   - Edit user information
   - Assign roles
   - Suspend/activate accounts
   - Delete users (with caution)
3. User manages:
   - User roles across organizations
   - Agency memberships
   - Platform permissions

**Key UI Elements:**
- User table
- Role selector
- Status badges
- "Edit User" button
- Organization/Agency associations

**Decision Points:**
- If user suspended → Cannot log in
- If role changed → Permissions updated
- User can assign any role → Full control

---

**Step 4: Moderate Content**
**Route:** `/admin/content`

**Actions:**
1. User views content moderation queue
2. User reviews:
   - Campaign content
   - User-generated content
   - Reported content
3. User can:
   - Approve content
   - Reject content
   - Delete content
   - Flag for review
4. User manages content policies

**Key UI Elements:**
- Content moderation queue
- Content preview
- Approve/Reject buttons
- Policy settings

---

**Step 5: Manage Subscription Packages**
**Route:** `/admin/packages`

**Actions:**
1. User views subscription plans
2. User can:
   - Create new packages
   - Edit existing packages
   - Set pricing
   - Configure features
   - Set usage limits
3. User manages:
   - Package tiers
   - Feature availability
   - Pricing models

**Key UI Elements:**
- Package list
- "Create Package" button
- Pricing fields
- Feature toggles
- Usage limit settings

---

**Step 6: View System Logs**
**Route:** `/admin/logs`

**Actions:**
1. User views application logs
2. User filters by:
   - Log level (info, warning, error)
   - Date range
   - User/organization
   - Action type
3. User can:
   - Search logs
   - Export logs
   - View error details
   - Track user actions

**Key UI Elements:**
- Log table
- Filter controls
- Search bar
- Log level badges
- Export button

---

**Step 7: Configure Platform Settings**
**Route:** `/admin/settings`

**Actions:**
1. User accesses platform settings
2. User configures:
   - System configuration
   - Feature flags
   - Global settings
   - Integration settings
   - Email templates
   - Notification settings
3. User saves changes

**Key UI Elements:**
- Settings tabs
- Configuration forms
- Feature flag toggles
- Save button

---

### Exit Points
- **To Organization View:** User clicks "Return to App" → Returns to organization selection
- **To Specific Organization:** User accesses organization directly → Full access to org data
- **Logout:** User ends session

### Common Variations

**Variation 1: Emergency Response**
- User responds to system alerts
- Investigates errors
- Takes corrective action

**Variation 2: User Support**
- User assists with user issues
- Resets passwords
- Resolves account problems

**Variation 3: Platform Updates**
- User deploys new features
- Updates settings
- Monitors rollout

---

## 6. Campaign Creation Journey

### Journey Overview
**User Type:** Organization Admin  
**Goal:** Create, plan, and launch marketing campaign  
**Duration:** 30-60 minutes (multi-step process)  
**Success Criteria:** Campaign created, content generated, submitted for review

### Detailed Step-by-Step Flow

#### Step 1: Access Campaign Creation
**Route:** `/main/[organizationId]/campaigns` → Click "Create New Campaign"

**Prerequisites:**
- Organization Admin role
- Brand selected (recommended)
- Channels configured (required)

**Actions:**
1. User navigates to campaigns page
2. User clicks "Create New Campaign" button
3. System redirects to `/main/[organizationId]/campaigns/create`
4. Campaign stepper shows "Plan" step active

---

#### Step 2: Plan Campaign
**Route:** `/main/[organizationId]/campaigns/create`

**Form Fields:**

**Campaign Goal:**
- Text input for campaign description
- Goal type selector:
  - Product Launch
  - Brand Awareness
  - Lead Generation
  - Event Promotion
  - Custom

**Product Selection (if applicable):**
- Dropdown populated with organization products
- Optional field

**Brand Selection:**
- Dropdown populated with organization brands
- Optional but recommended

**Goal Prompt/URL:**
- Optional URL input
- Used for AI context

**Channel Selection:**
- Checkboxes for each channel:
  - Facebook
  - Instagram
  - LinkedIn
  - Twitter/X
  - Pinterest
  - WhatsApp
  - Email
  - Influencer (opens dialog for details)

**AI Assistance:**
- "Get Suggestions" button
- AI generates campaign suggestions based on:
  - Organization data
  - Brand information
  - Product details
- User can click suggestion cards to use them

**Actions:**
1. User fills form fields
2. User selects channels
3. User optionally gets AI suggestions
4. User clicks "Generate Campaign Plan"
5. AI processes:
   - Campaign strategy
   - Content themes
   - Posting schedule
   - Channel-specific recommendations
6. User reviews generated plan
7. User can:
   - Edit plan
   - Regenerate plan
   - Continue to content generation

**Key UI Elements:**
- Campaign plan form
- Goal type selector
- Product/Brand dropdowns
- Channel checkboxes with icons
- AI suggestion cards
- "Generate Plan" button
- Campaign stepper

**Decision Points:**
- If no channels selected → Validation error, cannot proceed
- If plan generation fails → Error message, retry option
- User can save draft → Stored, can resume later

---

#### Step 3: Generate Content
**Route:** `/main/[organizationId]/campaigns/content?campaignId=[id]`

**Prerequisites:**
- Campaign plan completed
- Campaign ID generated

**Actions:**
1. User sees campaign plan summary
2. User reviews:
   - Campaign goal
   - Selected channels
   - Generated strategy
3. User clicks "Generate Initial Launch Content"
4. AI generates content for each channel:
   - Post copy (platform-optimized)
   - Image suggestions
   - Hashtags
   - Posting times
   - Content variations
5. User reviews generated content:
   - Channel-specific previews
   - Content cards for each post
   - Scheduling information
6. User can:
   - Edit individual posts → Opens edit dialog
   - Regenerate content → Creates new variations
   - Add more posts → Generates additional content
   - Adjust schedule → Opens schedule editor
   - Delete posts → Removes from campaign
7. User clicks "Submit for Review"
8. System:
   - Creates scheduled posts in database
   - Sets campaign status to "In Review"
   - Adds content to review queue
   - Sends notifications to reviewers
   - Updates campaign list

**Key UI Elements:**
- Campaign plan summary card
- Content generation button
- Channel content previews
- Post cards with edit buttons
- Schedule editor
- "Submit for Review" button
- Campaign stepper (shows "Content" step active)

**Decision Points:**
- If content needs editing → User edits before submission
- If user regenerates → New content replaces old
- If user submits → Campaign moves to review workflow

---

#### Step 4: Review & Approval
**Route:** `/main/[organizationId]/review?brandId=[brandId]`

**Prerequisites:**
- Content submitted for review
- User has review permissions

**Actions:**
1. User navigates to review page
2. User sees campaign content in review queue
3. User reviews each content item:
   - Opens review dialog
   - Reads content
   - Checks attachments
   - Reviews PDF annotations (if any)
4. User approves or rejects:
   - Clicks "Approve" → Content approved
   - Clicks "Reject" → Adds comments, returns to author
5. System updates:
   - Review status
   - Campaign status (if all approved → "Active")
   - Sends notifications

**Decision Points:**
- If all content approved → Campaign becomes "Active"
- If some rejected → Campaign stays "In Review"
- User can request changes → Rejects with comments

---

#### Step 5: Publish Campaign
**Route:** `/main/[organizationId]/campaigns`

**Prerequisites:**
- Campaign status: "Active"
- Content approved

**Actions:**
1. User views campaign in campaigns list
2. User sees "Active" status badge
3. User can:
   - View scheduled posts → Calendar view
   - Publish all now → Immediate publishing
   - Edit schedule → Adjust posting times
   - Deactivate campaign → Pauses publishing
4. System publishes posts:
   - According to schedule
   - To selected channels
   - With tracking enabled

**Key UI Elements:**
- Campaign list table
- Status badge (Active)
- "Publish All Now" button
- Schedule calendar
- Action menu

**Decision Points:**
- If user publishes now → Bypasses schedule
- If user deactivates → Campaign paused
- System publishes automatically → According to schedule

---

### Exit Points
- **To Campaigns List:** User returns to campaigns page
- **To Review:** User checks review queue
- **To Analytics:** User views campaign performance

### Common Variations

**Variation 1: Quick Campaign**
- User uses AI suggestions
- Pre-filled form
- Faster content generation
- Reduced manual input

**Variation 2: Multi-Channel Campaign**
- User selects multiple channels
- Content generated for each
- Platform-specific optimization
- Coordinated posting schedule

**Variation 3: Scheduled Campaign**
- User sets future start date
- Content prepared in advance
- Automatic publishing
- No immediate action needed

---

## 7. Content Review Journey

### Journey Overview
**User Type:** Organization Admin or Client Role  
**Goal:** Review and approve content before publication  
**Duration:** 5-15 minutes per review session  
**Success Criteria:** Content reviewed, approved/rejected, feedback provided

### Detailed Step-by-Step Flow

#### Step 1: Access Review Queue
**Route:** `/main/[organizationId]/review?brandId=[brandId]`

**Entry Points:**
- User clicks review badge in header
- User navigates from collaboration hub
- User receives review notification
- User accesses review menu item

**Prerequisites:**
- Brand selected
- Pending review items exist

**Actions:**
1. User sees review table
2. Table displays:
   - Channel badges (platform icons)
   - Content preview (truncated)
   - Author name
   - Submission date
   - Status badge (Pending Review/Approved/Rejected)
   - "View" button
3. User can filter by:
   - Status
   - Channel
   - Author
   - Date range
4. User sees pending count in header badge

**Key UI Elements:**
- Review table
- Status badges
- Channel badges
- Filter controls
- "View" buttons
- Review count badge

---

#### Step 2: Review Content Item
**Route:** Same page, review dialog opens

**Actions:**
1. User clicks "View" on content item
2. Review dialog opens showing:
   - Full content display
   - Channel information
   - Author details
   - Submission timestamp
   - Attachments (images/PDFs)
   - PDF annotation detection (if applicable)
3. User reviews:
   - Reads full content
   - Checks formatting
   - Reviews attachments
   - Checks for brand compliance
   - Verifies accuracy
4. User can:
   - Add comments (optional)
   - Request changes
   - Approve content
   - Reject content

**Key UI Elements:**
- Review dialog
- Full content display
- Attachment viewer
- PDF annotation viewer
- Comment input field
- Approve/Reject buttons

**Decision Points:**
- If content approved → User clicks "Approve"
- If content needs changes → User adds comments, clicks "Reject"
- If content perfect → User approves immediately

---

#### Step 3: Approve Content
**Action:** User clicks "Approve" button

**System Actions:**
1. Updates content status to "Approved"
2. Removes from pending queue
3. Updates review count badge
4. Sends notification to author
5. If all campaign content approved → Updates campaign status to "Active"
6. Content ready for publishing

**User Experience:**
- Success message displayed
- Content removed from pending list
- Badge count decreases
- Author receives notification

---

#### Step 4: Reject Content
**Action:** User clicks "Reject" button (after adding comments)

**System Actions:**
1. Updates content status to "Rejected"
2. Adds rejection comments
3. Returns content to author
4. Sends notification to author with comments
5. Campaign stays "In Review" (if part of campaign)
6. Author can revise and resubmit

**User Experience:**
- Rejection confirmation
- Comments saved
- Content moved to rejected status
- Author notified

---

#### Step 5: Bulk Review (Optional)
**Action:** User reviews multiple items in sequence

**Workflow:**
1. User reviews first item
2. User approves/rejects
3. Dialog closes, next item highlighted
4. User continues reviewing
5. Process repeats until queue empty

**Key UI Elements:**
- "Next" button in dialog
- Queue progress indicator
- Bulk actions (if implemented)

---

### Exit Points
- **To Collaboration Hub:** User returns to home
- **To Campaigns:** User views campaign details
- **Review Complete:** All items reviewed

### Common Variations

**Variation 1: Quick Approval**
- User approves without comments
- Faster workflow
- For trusted content creators

**Variation 2: Detailed Review**
- User adds extensive comments
- Requests specific changes
- Collaborative editing process

**Variation 3: PDF Annotation Review**
- PDF attachments detected
- User reviews annotations
- Approves/rejects based on annotations

---

## Journey Cross-References

### Related Documentation
- **Page Documentation:** See `PAGE_DOCUMENTATION.md` for detailed page specifications
- **Model Relationships:** See `MODEL_RELATIONSHIPS.md` for data structure
- **Features:** See `DETAILED_FEATURES.md` for feature specifications

### Key Journey Intersections

**Onboarding → Campaign Creation:**
- New users complete onboarding
- Create first brand
- Set up channels
- Create first campaign

**Campaign Creation → Review:**
- Content generated
- Submitted for review
- Review workflow
- Approval/rejection

**Review → Publishing:**
- Content approved
- Campaign activated
- Scheduled publishing
- Analytics tracking

**Organization Admin → Agency:**
- Admin can be agency member
- Switches to agency view
- Manages multiple clients
- Generates reports

**Agency → Client Organization:**
- Agency member accesses client
- Works within client context
- Creates content
- Manages campaigns

---

## Journey Metrics & Optimization

### Key Performance Indicators

**Onboarding Journey:**
- Completion rate
- Time to complete
- Drop-off points
- Brand creation rate

**Campaign Creation:**
- Campaign creation rate
- Content generation time
- Approval rate
- Publishing success rate

**Review Journey:**
- Review completion time
- Approval/rejection ratio
- Comment frequency
- Revision cycles

**Agency Journey:**
- Client management efficiency
- Task completion rate
- Report generation time
- Client satisfaction

### Optimization Opportunities

1. **Onboarding:**
   - Reduce steps for returning users
   - Pre-fill data where possible
   - Provide skip options

2. **Campaign Creation:**
   - Improve AI suggestions
   - Streamline form fields
   - Enable bulk operations

3. **Review:**
   - Reduce review time
   - Improve approval workflow
   - Enable bulk actions

4. **Agency:**
   - Improve cross-client visibility
   - Streamline reporting
   - Enhance task management

---

*Document Version: 1.0*  
*Last Updated: [Current Date]*  
*Prepared for: MarketPulse Laravel 12 Redevelopment*


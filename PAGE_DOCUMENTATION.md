# MarketPulse Application - Page Documentation

This document provides comprehensive documentation for each page in the Customer (Organization) and Agency panels, including their purpose, functionality, and Laravel 12 implementation considerations.

## Frontend Technology Stack

**Primary Approach:**
- **Blade Templates**: Main structure and server-side rendering (default)
- **Alpine.js**: Simple interactivity (dropdowns, toggles, form validation, modals, etc.)
- **Vue.js Components**: Complex, stateful UI sections (dashboards, Kanban boards, charts, real-time features)

**When to Use Each:**
- **Blade**: Page layouts, server-rendered content, forms, static pages, list views
- **Alpine.js**: Dropdowns, modals, toggles, simple show/hide, client-side validation
- **Vue.js**: Interactive dashboards, Kanban boards, charts, real-time chat, complex form builders

## Document Structure

For each page, you'll find:
1. **Route** - URL path and menu information
2. **Purpose** - What the page does
3. **How It Works** - Detailed functionality and user interactions
4. **Key Features** - Main capabilities
5. **Laravel 12 Considerations** - Implementation guidance and architectural notes

---

## UI LAYOUT AND STRUCTURE DOCUMENTATION

This section documents the overall UI layout, navigation structure, and component hierarchy for all three backend interfaces: Customer (Organization), Agency, and Admin panels.

### Overall Layout Architecture

All three backends follow a consistent layout pattern using a **Sidebar + Header + Main Content** structure:

```
┌─────────────────────────────────────────────────────────┐
│                    Header (Fixed Top)                    │
├──────────┬──────────────────────────────────────────────┤
│          │                                              │
│ Sidebar  │         Main Content Area                    │
│ (Fixed)  │         (Scrollable)                         │
│          │                                              │
│          │                                              │
└──────────┴──────────────────────────────────────────────┘
```

**Key Components:**
- **SidebarProvider**: Wraps the entire layout, manages sidebar state
- **Sidebar**: Left navigation panel (collapsible)
- **SidebarInset**: Main content area wrapper
- **Header**: Top navigation bar (sticky)
- **Main**: Scrollable content container

---

## CUSTOMER PANEL (Organization Panel) - UI Structure

**Route Pattern:** `/main/[organizationId]/*`  
**Layout File:** `src/app/(app)/layout.tsx`  
**Sidebar Component:** `src/components/layout/app-sidebar.tsx`

### Layout Structure

#### 1. Sidebar Navigation (`AppSidebar`)

**Structure:**
```
┌─────────────────────────┐
│  SidebarHeader          │
│  - MarketPulse Logo     │
│  - Brand Name           │
├─────────────────────────┤
│  SidebarContent         │
│  ┌─────────────────────┐│
│  │ Brand Switcher      ││ (if brands exist)
│  └─────────────────────┘│
│  ┌─────────────────────┐│
│  │ Home                ││
│  │ Brand Assets        ││ (if brand selected)
│  │ Campaigns ▼         ││
│  │   - Campaigns       ││
│  │   - Competitions    ││
│  │ Projects            ││
│  │ Tasks               ││
│  │ Chatbots            ││
│  │ Landing Pages       ││
│  ├─────────────────────┤│
│  │ Review              ││ (if brand selected)
│  │ Files               ││ (if brand selected)
│  │ Analytics           ││ (if brand selected)
│  ├─────────────────────┤│
│  │ Brands              ││ (if no brand selected)
│  │ Channels            ││ (if no brand selected)
│  │ Products            ││ (if no brand selected)
│  │ Contacts            ││ (if no brand selected)
│  │ Email Marketing ▼   ││
│  │   - Email Campaigns ││
│  │   - Surveys         ││
│  │ Paid Ads ▼          ││
│  │   - Ad Campaigns   ││
│  │   - Ad Copy Gen    ││
│  │   - Keyword Res    ││
│  │ Content Ideation ▼ ││
│  │   - SEO Analysis   ││
│  │   - Email Template ││
│  │   - Label Insp     ││
│  │   - Image Gen      ││
│  │   - Product Cat    ││
│  │ Intelligence ▼      ││
│  │   - Sentiment      ││
│  │   - Predictive     ││
│  │   - Competitor     ││
│  └─────────────────────┘│
│  ┌─────────────────────┐│
│  │ Organization ▼      ││ (if admin)
│  │   - Settings        ││
│  │   - Billing         ││
│  │   - Team Members    ││
│  │   - Storage Sources ││
│  │   - Automations     ││
│  └─────────────────────┘│
├─────────────────────────┤
│  SidebarFooter          │
│  - Sidebar Toggle       │
│  - User Menu            │
└─────────────────────────┘
```

**Key Features:**
- **Collapsible Sections**: Campaigns, Email Marketing, Paid Ads, Content Ideation, Intelligence, Organization
- **Context-Aware Menu Items**: Menu items show/hide based on:
  - Brand selection (brandId query parameter)
  - User role (Client vs Admin)
  - Organization state
- **Badge Counts**: 
  - Review items (red badge)
  - Mentions (red badge on Home)
  - Campaign count (secondary badge)
  - Project count (secondary badge)
- **Brand Switcher**: Dropdown to select active brand (shown when brands exist)
- **Responsive**: Collapses to icon-only on mobile, expands to full sidebar on desktop
- **Tooltips**: Show full labels when collapsed

**Menu Item Visibility Rules:**
- **Always Visible** (when organizationId exists):
  - Home
- **Brand Context Items** (only when brandId is selected):
  - Brand Assets
  - Review
  - Files
  - Analytics
- **No Brand Context Items** (only when no brandId):
  - Brands, Channels, Products, Contacts
  - Email Marketing, Paid Ads, Content Ideation, Intelligence
- **Role-Based Items** (hidden for Client role):
  - Campaigns, Projects, Tasks, Chatbots, Landing Pages
- **Admin-Only Items** (only for Organization Admins):
  - Organization section (Settings, Billing, Team, Storage, Automations)

#### 2. Role-Based Permissions and Visibility

**Available Roles in Customer Panel:**

1. **Client Role** (Limited Access)
   - **Purpose**: Read-only or limited access for stakeholders who need to view content but not create/edit
   - **Menu Items Visible**:
     - ✅ Home (Collaboration)
     - ✅ Brand Assets (when brand selected)
     - ✅ Review (when brand selected)
     - ✅ Files (when brand selected)
     - ✅ Analytics (when brand selected)
     - ✅ Brands (view only, when no brand selected)
     - ✅ Channels (view only, when no brand selected)
     - ✅ Products (view only, when no brand selected)
     - ✅ Contacts (view only, when no brand selected)
   - **Menu Items Hidden**:
     - ❌ Campaigns (all sub-items)
     - ❌ Projects
     - ❌ Tasks
     - ❌ Chatbots
     - ❌ Landing Pages
     - ❌ Email Marketing
     - ❌ Paid Ads
     - ❌ Content Ideation
     - ❌ Intelligence
     - ❌ Organization settings (all admin items)
   - **Page Access**:
     - Can view collaboration hub
     - Can view and approve/reject review items
     - Can view brand assets and files
     - Can view analytics (read-only)
     - Cannot create campaigns, projects, or tasks
     - Cannot access organization settings

2. **Admin Role** (Organization Admin - Full Access)
   - **Purpose**: Full administrative access to organization resources
   - **Menu Items Visible**:
     - ✅ All menu items available to Client role
     - ✅ Campaigns (create, edit, manage)
     - ✅ Projects (create, edit, manage)
     - ✅ Tasks (create, edit, manage)
     - ✅ Chatbots (create, edit, manage)
     - ✅ Landing Pages (create, edit, manage)
     - ✅ Email Marketing (all features)
     - ✅ Paid Ads (all features)
     - ✅ Content Ideation (all tools)
     - ✅ Intelligence (all tools)
     - ✅ Organization section:
       - ✅ Settings
       - ✅ Billing
       - ✅ Team Members
       - ✅ Storage Sources
       - ✅ Automations
   - **Page Access**:
     - Full access to all pages
     - Can create, edit, and delete campaigns
     - Can manage team members and roles
     - Can configure organization settings
     - Can manage billing and subscriptions
     - Can create and manage automations
   - **Special Permissions**:
     - Can assign roles to team members
     - Can manage organization-wide settings
     - Can access billing and subscription management
     - Can configure storage integrations
     - Can create and manage workflows/automations

3. **Site Admin** (Super Admin - Platform Access)
   - **Purpose**: Platform-level administration (separate admin panel)
   - **Access**: Separate `/admin/*` routes
   - **Not applicable to Customer Panel** (uses Admin Panel instead)

**Permission Matrix - Customer Panel:**

| Feature/Page | Client Role | Admin Role |
|--------------|-------------|------------|
| **Home (Collaboration)** | ✅ View | ✅ View |
| **Brand Assets** | ✅ View | ✅ View/Manage |
| **Review** | ✅ Approve/Reject | ✅ Approve/Reject |
| **Files** | ✅ View | ✅ View/Upload/Manage |
| **Analytics** | ✅ View | ✅ View |
| **Brands** | ✅ View | ✅ View/Create/Edit/Delete |
| **Channels** | ✅ View | ✅ View/Create/Edit/Delete |
| **Products** | ✅ View | ✅ View/Create/Edit/Delete |
| **Contacts** | ✅ View | ✅ View/Create/Edit/Delete |
| **Campaigns** | ❌ Hidden | ✅ Full Access |
| **Projects** | ❌ Hidden | ✅ Full Access |
| **Tasks** | ❌ Hidden | ✅ Full Access |
| **Chatbots** | ❌ Hidden | ✅ Full Access |
| **Landing Pages** | ❌ Hidden | ✅ Full Access |
| **Email Marketing** | ❌ Hidden | ✅ Full Access |
| **Paid Ads** | ❌ Hidden | ✅ Full Access |
| **Content Ideation** | ❌ Hidden | ✅ Full Access |
| **Intelligence** | ❌ Hidden | ✅ Full Access |
| **Settings** | ❌ Hidden | ✅ Full Access |
| **Billing** | ❌ Hidden | ✅ Full Access |
| **Team Members** | ❌ Hidden | ✅ Full Access |
| **Storage Sources** | ❌ Hidden | ✅ Full Access |
| **Automations** | ❌ Hidden | ✅ Full Access |

**Implementation Notes:**
- Role checking is done via `currentUser?.role === 'Client'` comparison
- Organization admin status checked via `isUserAdminForOrg` flag
- Menu items conditionally rendered based on role checks
- Page-level access control should be implemented in route middleware
- API endpoints should verify permissions server-side

#### 2. Header Component

**Structure:**
```
┌─────────────────────────────────────────────────────────┐
│ [☰] Page Title    [Org Switcher] [📅] [✓] [🔔]         │
└─────────────────────────────────────────────────────────┘
```

**Components:**
- **Mobile Menu Toggle**: Hamburger icon (visible on mobile only)
- **Page Title**: Dynamic title based on current route
- **Organization Switcher**: Dropdown to switch between organizations
- **Calendar Dialog**: Quick access to calendar view
- **Review Indicator**: Shows pending review count
- **Notifications**: Notification bell with badge

**Styling:**
- Sticky positioning (`sticky top-0`)
- Backdrop blur effect (`backdrop-blur-sm`)
- Border bottom separator
- Responsive padding

#### 3. Main Content Area

**Structure:**
```tsx
<main className="flex-1 overflow-y-auto py-4 md:py-6 lg:py-8">
  <div className="container mx-auto">
    {children}
  </div>
</main>
```

**Features:**
- Scrollable container (`overflow-y-auto`)
- Responsive padding (increases on larger screens)
- Centered container with max-width
- Full height flex layout

#### 4. Mobile Responsive Behavior

**Desktop (md and above):**
- Sidebar always visible (can be collapsed)
- Full sidebar with labels
- Header shows all controls

**Mobile (below md):**
- Sidebar hidden by default
- Hamburger menu opens sidebar in Sheet overlay
- Sidebar closes when menu item selected
- Compact header layout

#### 5. Special Components

**Command Popover (AI Assistant):**
- Fixed position bottom-right (`fixed bottom-6 right-6`)
- Floating action button for AI assistant
- Opens command dialog for quick actions

**Brand Switcher:**
- Dropdown component in sidebar header
- Shows when organization has brands
- Updates URL query parameter (`?brandId=...`)
- Triggers menu visibility changes

**Organization Switcher:**
- Dropdown in header
- Allows switching between organizations
- Updates route to new organization context

---

## AGENCY PANEL - UI Structure

**Route Pattern:** `/agency/[agencyId]/*`  
**Layout File:** `src/app/(agency)/layout.tsx`  
**Sidebar Component:** `src/components/layout/agency-sidebar.tsx`

### Layout Structure

#### 1. Sidebar Navigation (`AgencySidebar`)

**Structure:**
```
┌─────────────────────────┐
│  SidebarHeader          │
│  - Briefcase Icon       │
│  - "Agency View" Text    │
├─────────────────────────┤
│  SidebarContent         │
│  ┌─────────────────────┐│
│  │ Clients             ││
│  │ Tasks               ││
│  │ Aggregated Calendar ││
│  │ Billing & Invoicing ││
│  │ Reporting           ││
│  │ Team Management     ││
│  │ Agency Settings     ││
│  └─────────────────────┘│
├─────────────────────────┤
│  SidebarFooter          │
│  - User Menu            │
│    - Exit Agency View   │
│    - Log out            │
└─────────────────────────┘
```

**Key Features:**
- **Flat Menu Structure**: No collapsible sections (simpler navigation)
- **Agency-Specific Items**: All items relate to agency operations
- **Client Management**: Primary focus on client organizations
- **Cross-Client Views**: Tasks and Calendar aggregate across all clients
- **Billing Focus**: Dedicated billing and invoicing section
- **Reporting Tools**: AI-powered report generation for clients

**Menu Items:**
1. **Clients** (`/agency/[agencyId]/clients`)
   - Default route (redirects from `/agency/[agencyId]`)
   - Lists all client organizations
   - Shows user counts per client
   - Links to client organization views

2. **Tasks** (`/agency/[agencyId]/tasks`)
   - Unified Kanban board
   - Shows tasks from all clients
   - Client badges on task cards

3. **Aggregated Calendar** (`/agency/[agencyId]/calendar`)
   - Full calendar view
   - All client events combined
   - Color-coded by client

4. **Billing & Invoicing** (`/agency/[agencyId]/billing`)
   - Invoice management
   - Payment tracking
   - Automated reminders

5. **Reporting** (`/agency/[agencyId]/reports`)
   - AI report generation
   - Client selection
   - Report type selection

6. **Team Management** (`/agency/[agencyId]/team`)
   - Agency team members
   - Role management
   - Client access permissions

7. **Agency Settings** (`/agency/[agencyId]/settings`)
   - Agency profile
   - Branding configuration
   - Default settings

#### 2. Role-Based Permissions and Visibility

**Available Roles in Agency Panel:**

1. **Agency Member** (Standard Access)
   - **Purpose**: Standard agency team member who can work with clients
   - **Menu Items Visible**:
     - ✅ Clients (view and access)
     - ✅ Tasks (view and manage)
     - ✅ Aggregated Calendar (view)
     - ✅ Reporting (generate reports)
   - **Menu Items Hidden**:
     - ❌ Billing & Invoicing (admin only)
     - ❌ Team Management (admin only)
     - ❌ Agency Settings (admin only)
   - **Page Access**:
     - Can view and access client organizations
     - Can create and manage tasks across clients
     - Can view aggregated calendar
     - Can generate reports for clients
     - Cannot manage billing/invoicing
     - Cannot manage team members
     - Cannot modify agency settings

2. **Agency Admin** (Full Agency Access)
   - **Purpose**: Full administrative access to agency operations
   - **Menu Items Visible**:
     - ✅ All menu items available to Agency Member
     - ✅ Billing & Invoicing (full access)
     - ✅ Team Management (full access)
     - ✅ Agency Settings (full access)
   - **Page Access**:
     - Full access to all agency pages
     - Can manage invoices and payments
     - Can add/remove team members
     - Can assign roles and permissions
     - Can configure agency settings
     - Can manage client relationships
   - **Special Permissions**:
     - Can manage agency billing and invoicing
     - Can add/remove agency team members
     - Can assign roles to team members
     - Can configure agency-wide settings
     - Can manage client access permissions

**Permission Matrix - Agency Panel:**

| Feature/Page | Agency Member | Agency Admin |
|--------------|---------------|--------------|
| **Clients** | ✅ View/Access | ✅ View/Access/Manage |
| **Tasks** | ✅ View/Create/Edit | ✅ View/Create/Edit |
| **Aggregated Calendar** | ✅ View | ✅ View |
| **Reporting** | ✅ Generate Reports | ✅ Generate Reports |
| **Billing & Invoicing** | ❌ Hidden | ✅ Full Access |
| **Team Management** | ❌ Hidden | ✅ Full Access |
| **Agency Settings** | ❌ Hidden | ✅ Full Access |

**Implementation Notes:**
- Agency role checking should verify user's role within the agency context
- Billing access restricted to agency admins only
- Team management requires admin permissions
- Client access may be further restricted based on client assignment

#### 3. Header Component

**Structure:**
- Same Header component as Customer Panel
- Shows dynamic page title
- Mobile menu toggle
- No organization switcher (agency context is fixed)

#### 3. Main Content Area

**Structure:**
- Same structure as Customer Panel
- Scrollable container
- Responsive padding
- Centered content

#### 4. User Menu (Footer)

**Options:**
- **Exit Agency View**: Returns to organization selection
- **Log out**: Logs out user

---

## ADMIN PANEL - UI Structure

**Route Pattern:** `/admin/*`  
**Layout File:** `src/app/admin/layout.tsx`  
**Sidebar Component:** `src/components/layout/admin-sidebar.tsx`

### Layout Structure

#### 1. Sidebar Navigation (`AdminSidebar`)

**Structure:**
```
┌─────────────────────────┐
│  SidebarHeader          │
│  - Shield Icon          │
│  - "Admin Panel" Text   │
│  (Dark Background)      │
├─────────────────────────┤
│  SidebarContent         │
│  ┌─────────────────────┐│
│  │ Dashboard           ││
│  │ Organizations       ││
│  │ Users               ││
│  │ Content             ││
│  │ Packages            ││
│  │ Costing             ││
│  │ Billing             ││
│  │ Agency Team         ││
│  │ System Logs         ││
│  │ Platform Settings   ││
│  └─────────────────────┘│
├─────────────────────────┤
│  SidebarFooter          │
│  - User Menu            │
│    - Return to App      │
│    - Log out            │
└─────────────────────────┘
```

**Key Features:**
- **Dark Theme**: Dark background (`bg-gray-900`) with light text
- **Flat Menu Structure**: Simple list navigation
- **Platform Management**: Focus on system-wide administration
- **High Contrast**: Active items use primary color with background highlight

**Menu Items:**
1. **Dashboard** (`/admin/dashboard`)
   - System overview
   - Key metrics
   - Platform health

2. **Organizations** (`/admin/organizations`)
   - Manage all organizations
   - Organization details
   - Status management

3. **Users** (`/admin/users`)
   - User management
   - Role assignment
   - Account status

4. **Content** (`/admin/content`)
   - Content moderation
   - Content review
   - Content management

5. **Packages** (`/admin/packages`)
   - Subscription packages
   - Package configuration
   - Pricing management

6. **Costing** (`/admin/costing`)
   - Cost analysis
   - Pricing models
   - Cost tracking

7. **Billing** (`/admin/billing`)
   - Billing management
   - Payment processing
   - Invoice oversight

8. **Agency Team** (`/admin/team`)
   - Agency management
   - Team oversight
   - Agency settings

9. **System Logs** (`/admin/logs`)
   - Application logs
   - Error tracking
   - Audit trail

10. **Platform Settings** (`/admin/settings`)
    - System configuration
    - Feature flags
    - Global settings

#### 2. Role-Based Permissions and Visibility

**Available Roles in Admin Panel:**

1. **Admin** (Platform Administrator - Full Access)
   - **Purpose**: Complete platform administration and oversight
   - **Menu Items Visible**:
     - ✅ Dashboard
     - ✅ Organizations (view/manage all)
     - ✅ Users (view/manage all)
     - ✅ Content (moderate all)
     - ✅ Packages (manage subscription plans)
     - ✅ Costing (view/manage pricing)
     - ✅ Billing (oversee all billing)
     - ✅ Agency Team (manage agencies)
     - ✅ System Logs (view all logs)
     - ✅ Platform Settings (configure system)
   - **Page Access**:
     - Full access to all admin pages
     - Can view and manage all organizations
     - Can view and manage all users
     - Can moderate all content
     - Can create/edit subscription packages
     - Can configure platform-wide settings
     - Can view system logs and errors
     - Can manage agencies and their teams
   - **Special Permissions**:
     - Can access any organization's data
     - Can modify user roles and permissions
     - Can approve/reject content globally
     - Can configure feature flags
     - Can manage platform billing
     - Can view sensitive system information

**Permission Matrix - Admin Panel:**

| Feature/Page | Admin Role |
|--------------|------------|
| **Dashboard** | ✅ Full Access |
| **Organizations** | ✅ View/Create/Edit/Delete |
| **Users** | ✅ View/Create/Edit/Delete/Assign Roles |
| **Content** | ✅ Moderate/Approve/Reject/Delete |
| **Packages** | ✅ Create/Edit/Delete/Manage Pricing |
| **Costing** | ✅ View/Manage Pricing Models |
| **Billing** | ✅ View/Manage All Billing |
| **Agency Team** | ✅ View/Manage Agencies |
| **System Logs** | ✅ View All Logs |
| **Platform Settings** | ✅ Configure System |

**Implementation Notes:**
- Admin panel access requires `user_type = 'admin'` or `role = 'admin'`
- All admin routes should be protected by admin middleware
- Admin users have unrestricted access to platform data
- Sensitive operations should require additional confirmation
- All admin actions should be logged for audit purposes

#### 3. Header Component

**Structure:**
- Same Header component
- Shows "Admin" or section name
- Mobile menu toggle
- No organization switcher

#### 3. Main Content Area

**Structure:**
- Same structure as other panels
- Dark theme consistent with sidebar

#### 4. User Menu (Footer)

**Options:**
- **Return to App**: Returns to organization selection
- **Log out**: Logs out user

---

## COMMON UI PATTERNS

### Sidebar Component Structure

All sidebars use the same base structure:

```tsx
<Sidebar>
  <SidebarHeader>
    {/* Logo/Branding */}
  </SidebarHeader>
  <SidebarContent>
    <SidebarMenu>
      {/* Menu Items */}
    </SidebarMenu>
  </SidebarContent>
  <SidebarFooter>
    {/* User Menu / Actions */}
  </SidebarFooter>
</Sidebar>
```

### Responsive Sidebar Behavior

**Desktop:**
- Sidebar visible in normal flow
- Can be collapsed to icon-only
- Uses `SidebarToggle` component
- Tooltips show on hover when collapsed

**Mobile:**
- Sidebar hidden by default
- Opens in `Sheet` overlay (left side)
- Full width sidebar (`w-64`)
- Closes on navigation or outside click

### Active State Management

**Route Matching:**
- Uses `usePathname()` to detect current route
- Compares route with menu item href
- Sets `isActive` prop on `SidebarMenuButton`
- Active items highlighted with primary color

**Query Parameters:**
- Brand context managed via `brandId` query parameter
- Organization context via route parameter
- Sidebar updates based on context

### Badge System

**Badge Types:**
- **Red Badge**: Urgent items (reviews, mentions)
- **Secondary Badge**: Count indicators (campaigns, projects)
- **Position**: Right side of menu item label
- **Visibility**: Hidden when sidebar collapsed, shown in tooltip

### User Menu Pattern

All panels include a user menu in the sidebar footer:

**Structure:**
- Avatar with user image
- User name and email
- Dropdown menu with actions:
  - Account settings
  - Billing (where applicable)
  - Navigation options (Exit view, Return to app)
  - Logout

### Layout Responsiveness

**Breakpoints:**
- Mobile: `< 768px` (md breakpoint)
- Tablet: `768px - 1024px`
- Desktop: `> 1024px`

**Responsive Classes:**
- `hidden md:flex`: Hide on mobile, show on desktop
- `md:hidden`: Show on mobile, hide on desktop
- `py-4 md:py-6 lg:py-8`: Responsive padding

### Component Hierarchy

```
App Layout (Root)
├── SidebarProvider
│   ├── Sidebar (Left Navigation)
│   │   ├── SidebarHeader
│   │   ├── SidebarContent
│   │   │   └── SidebarMenu
│   │   │       └── SidebarMenuItem[]
│   │   └── SidebarFooter
│   │       └── User Dropdown
│   └── SidebarInset (Main Content)
│       ├── Header (Top Navigation)
│       │   ├── Mobile Menu Toggle
│       │   ├── Page Title
│       │   └── Header Actions
│       │       ├── Organization Switcher
│       │       ├── Calendar Dialog
│       │       ├── Review Indicator
│       │       └── Notifications
│       └── Main Content Area
│           └── Page Content (children)
└── Command Popover (AI Assistant - Customer Panel only)
```

### State Management

**Layout State:**
- Sidebar collapse state (managed by SidebarProvider)
- Mobile menu open state (local component state)
- Organization selection (route parameter)
- Brand selection (query parameter)

**Context Passing:**
- Organization ID passed via route params
- Brand ID passed via query params
- State updates trigger sidebar re-render
- Menu visibility updates based on context

---

## IMPLEMENTATION NOTES

### Sidebar Collapsible Sections

Customer Panel uses `SidebarCollapsible` components for grouped menu items:

```tsx
<SidebarCollapsible>
  <SidebarCollapsibleTrigger icon={Icon} label="Section Name" />
  <SidebarCollapsibleContent>
    {/* Nested menu items */}
  </SidebarCollapsibleContent>
</SidebarCollapsible>
```

**Features:**
- Expandable/collapsible sections
- Icon and label on trigger
- Smooth animations
- State persistence (can set `defaultOpen`)

### Dynamic Menu Rendering

Menu items are conditionally rendered based on:
1. **Organization Context**: Items only show when `organizationId` exists
2. **Brand Context**: Brand-specific items show when `brandId` is selected
3. **User Role**: Client role hides certain items
4. **Admin Status**: Organization admin items only for admins

### URL Structure

**Customer Panel:**
- Base: `/main/[organizationId]/[page]`
- With Brand: `/main/[organizationId]/[page]?brandId=[brandId]`

**Agency Panel:**
- Base: `/agency/[agencyId]/[page]`

**Admin Panel:**
- Base: `/admin/[page]`

### Navigation Flow

**Customer Panel:**
1. User selects organization → Route updates → Sidebar shows org menu
2. User selects brand → Query param updates → Brand items appear
3. User navigates page → Route updates → Active state updates

**Agency Panel:**
1. User enters agency view → Route set → Sidebar shows agency menu
2. User navigates page → Route updates → Active state updates

**Admin Panel:**
1. User enters admin → Route set → Sidebar shows admin menu
2. User navigates page → Route updates → Active state updates

---

## ACCESSIBILITY CONSIDERATIONS

### Keyboard Navigation
- Sidebar items focusable via Tab
- Enter/Space activates menu items
- Escape closes mobile menu
- Arrow keys navigate menu items

### Screen Reader Support
- Semantic HTML structure
- ARIA labels on interactive elements
- Screen reader text for icons (`sr-only`)
- Proper heading hierarchy

### Visual Indicators
- Active state clearly visible
- Hover states for interactive elements
- Focus indicators for keyboard navigation
- Badge counts for important items

---

## STYLING AND THEMING

### Color Scheme

**Customer Panel:**
- Light theme (default)
- Primary color for active states
- Muted backgrounds for hover states

**Agency Panel:**
- Light theme (default)
- Consistent with Customer Panel

**Admin Panel:**
- Dark theme (`bg-gray-900`)
- Light text (`text-white`)
- Primary color accents

### Spacing

**Consistent Padding:**
- Sidebar: `p-2` for items
- Header: `px-4 md:px-6`
- Main content: `py-4 md:py-6 lg:py-8`
- Container: `container mx-auto`

### Typography

**Font Sizes:**
- Page titles: `text-xl font-semibold`
- Sidebar labels: Default size
- User info: `text-sm` and `text-xs`
- Badges: Small text

---

This UI structure documentation provides a comprehensive overview of how all three backend interfaces are organized and how they differ in navigation and layout patterns.

## Laravel 12 Implementation Approach

**TODO:** Review and update all implementation examples in this document to use Blade Templates + Alpine.js + Vue.js instead of Livewire references.

This application should be built using Laravel 12 following these principles:

- **MVC Architecture**: Use Laravel's Model-View-Controller pattern with clear separation of concerns
- **Service Layer**: Implement service classes to handle business logic, keeping controllers thin
- **Form Requests**: Use Form Request classes for validation and authorization
- **Policies**: Implement authorization policies for resource access control
- **Eloquent Relationships**: Leverage Eloquent relationships defined in MODEL_RELATIONSHIPS.md
- **Middleware**: Use middleware for organization/agency access control and authentication
- **Queue Jobs**: Implement queue jobs for long-running tasks (AI processing, email sending, etc.)
- **Events & Listeners**: Use events for decoupled actions and side effects
- **API Resources**: Use API Resources if building API endpoints for frontend consumption
- **Database Migrations**: Follow Laravel migration conventions for schema management
- **Caching**: Implement caching strategies for expensive queries and frequently accessed data
- **Eager Loading**: Use eager loading to prevent N+1 query problems

### Frontend Technology Stack

**Primary Approach:**
- **Blade Templates**: Main structure and server-side rendering
- **Alpine.js**: Simple interactivity (dropdowns, toggles, form validation, modals, etc.)
- **Vue.js Components**: Complex, stateful UI sections (dashboards, Kanban boards, charts, real-time features)

**When to Use Each:**

1. **Blade Templates** (Default):
   - Page layouts and structure
   - Server-rendered content
   - Forms with standard validation
   - Static or mostly static pages
   - List views and tables

2. **Alpine.js** (Simple Interactivity):
   - Dropdown menus and navigation
   - Modal dialogs
   - Toggle switches and accordions
   - Form field interactions
   - Simple show/hide states
   - Client-side form validation
   - Tab navigation

3. **Vue.js Components** (Complex Stateful UI):
   - Interactive dashboards with real-time updates
   - Kanban boards with drag-and-drop
   - Complex data visualizations (charts, graphs)
   - Real-time chat interfaces
   - Advanced form builders
   - File uploaders with progress
   - Calendar components with interactions
   - Data tables with sorting/filtering/pagination

**Implementation Pattern:**
- Controllers return Blade views with data
- Blade templates include Alpine.js directives for interactivity
- Complex sections use Vue components mounted to specific DOM elements
- Vue components communicate with backend via API endpoints or form submissions
- Use Laravel Broadcasting for real-time features (with Vue components)

---

## CUSTOMER PANEL (Organization Panel)

The Customer Panel is accessed via `/main/[organizationId]/*` routes and provides marketing management tools for organizations.

### Main Menu Structure

#### 1. Home (Collaboration)
**Route:** `/main/[organizationId]/collaboration`  
**Menu Label:** Home  
**Icon:** Home

**Purpose:**
Central hub for team collaboration, displaying pending reviews, recent activity, and team discussions.

**How It Works:**
- **Wall Feed View:** Default view showing:
  - Welcome card with instructions
  - "For Your Review" section displaying up to 3 pending content items requiring approval
  - Recent activity feed showing recent actions (approvals, team additions, calendar events, campaign creation)
- **Topic List Sidebar:** Left sidebar displaying:
  - Topics (hashtag conversations)
  - Direct Messages (DM conversations)
  - Unread count badges on topics
- **Chat Panel:** When a topic is selected:
  - Opens full chat interface for the selected topic
  - Supports real-time messaging
  - Displays message history
- **Review Integration:** 
  - Shows pending review items from campaigns
  - Quick approve/reject actions
  - Links to full review page for more items
- **Data Storage:** Uses localStorage for review items and campaign statuses, filtered by organizationId

**Key Features:**
- Real-time collaboration through topics and DMs
- Quick review actions for pending content
- Activity feed for workspace visibility
- Responsive design with mobile menu support

**Laravel 12 Considerations:**
- Implement real-time features using Laravel Broadcasting with Pusher or Laravel Echo
- Use Vue.js components for reactive chat interfaces with real-time updates
- Create a CollaborationController with index and showTopic methods
- Implement ReviewService to handle review item retrieval and filtering
- Use ActivityLog model for tracking user actions and displaying recent activity
- Implement ChatTopic and ChatMessage models with proper relationships
- Use database queries instead of localStorage for persistence
- Implement proper authorization policies to ensure users can only access their organization's data
- Use eager loading for chat topics, messages, and review items to optimize queries
- Consider implementing WebSocket connections for real-time message updates

---

#### 2. Brand Assets
**Route:** `/main/[organizationId]/brand-assets`  
**Menu Label:** Brand Assets  
**Icon:** Building  
**Visibility:** Only shown when a brand is selected

**Purpose:**
View and manage brand-specific assets, guidelines, and visual elements for the selected brand.

**How It Works:**
- Displays brand assets when a brand is selected from the brand switcher
- Shows brand guidelines, logos, colors, and other brand-specific resources
- Context-aware: Only accessible when viewing a specific brand

**Key Features:**
- Brand-scoped asset management
- Visual brand guidelines display
- Asset organization and categorization

---

#### 3. Campaigns
**Route:** `/main/[organizationId]/campaigns`  
**Menu Label:** Campaigns  
**Icon:** Megaphone  
**Visibility:** Hidden for Client role users

**Purpose:**
Create, manage, and track marketing campaigns across multiple channels.

**How It Works:**
- **Campaign List:** Table view showing all campaigns with:
  - Campaign name
  - Associated brand
  - Status (Active, Completed, Draft, In Review, Inactive)
  - Channels (social media platforms)
  - Next launch date
  - Action menu
- **Status Tabs:**
  - All: Active campaigns (excluding Drafts and Inactive)
  - Active: Currently running campaigns
  - Drafts: Campaigns being created/edited
  - In Review: Campaigns awaiting approval
  - Archived: Completed or Inactive campaigns
- **Campaign Actions:**
  - Create New Campaign: Opens campaign creation wizard
  - Continue Editing: For Draft campaigns
  - Go to Review: For In Review campaigns
  - View Details: View campaign information
  - Publish All Now: Immediately publish all scheduled posts
  - Deactivate/Reactivate: Toggle campaign status
  - Delete: Remove campaign (only for Drafts/In Review)
- **Data Management:**
  - Campaigns are grouped from scheduled posts by campaign name
  - Status stored in localStorage (`campaign_statuses_${organizationId}`)
  - Draft campaigns stored in localStorage with keys like `basis_${campaignId}`
  - Filters by brandId if brand context is active
- **Campaign Creation Flow:**
  - Multi-step wizard for campaign planning
  - AI-guided content generation
  - Content submission for review

**Key Features:**
- Multi-channel campaign management
- Status-based organization
- Brand filtering
- Quick publish actions
- Draft management
- Review workflow integration

**Laravel 12 Considerations:**
- Create CampaignController with CRUD operations and status management methods
- Implement CampaignService to handle campaign business logic (creation, status updates, publishing)
- Use Form Request classes for campaign creation and update validation
- Implement CampaignPolicy for authorization checks
- Use database storage instead of localStorage for campaign statuses and drafts
- Create Campaign model with relationships to Organization, Brand, Channel, and ScheduledPost
- Implement campaign status state machine (draft → in_review → active → completed/inactive)
- Use queue jobs for publishing campaigns to avoid long-running requests
- Implement campaign filtering using Eloquent query scopes
- Use database transactions when updating campaign status and associated posts
- Consider implementing campaign templates for reusable campaign structures
- Use event listeners to trigger notifications when campaigns are published or status changes

---

#### 4. Competitions
**Route:** `/main/[organizationId]/campaigns/competitions`  
**Menu Label:** Competitions  
**Icon:** Award  
**Visibility:** Hidden for Client role users

**Purpose:**
Manage marketing competitions and contests as part of campaign strategies.

**How It Works:**
- Sub-page under Campaigns section
- Manages competition-specific campaigns
- Tracks competition entries and results
- Integrates with campaign management system

**Key Features:**
- Competition campaign creation
- Entry tracking
- Results management

---

#### 5. Projects
**Route:** `/main/[organizationId]/projects`  
**Menu Label:** Projects  
**Icon:** Shapes  
**Visibility:** Hidden for Client role users

**Purpose:**
Manage marketing projects, track progress, and organize work across teams.

**How It Works:**
- Displays list of projects for the organization
- Project cards showing key information
- Project creation and editing
- Progress tracking
- Team assignment

**Key Features:**
- Project lifecycle management
- Team collaboration
- Progress tracking
- Project templates

---

#### 6. Tasks
**Route:** `/main/[organizationId]/tasks`  
**Menu Label:** Tasks  
**Icon:** ClipboardList  
**Visibility:** Hidden for Client role users

**Purpose:**
Task management system with Kanban-style board for organizing work.

**How It Works:**
- **Kanban Board:** Three columns:
  - To Do: New tasks
  - In Progress: Active tasks
  - Done: Completed tasks
- **Task Cards:** Display:
  - Task title
  - Due date
  - Assignee avatar
  - Edit and more options (on hover)
- **Drag and Drop:**
  - Tasks can be dragged between status columns
  - Visual feedback during drag operations
- **Task Details Dialog:**
  - Full task information
  - Description
  - Due date
  - Discussion panel (chat integration)
- **Task Form:**
  - Create/edit tasks
  - Assign to team members
  - Set due dates
  - Add descriptions
- **Data:** Tasks filtered by organizationId

**Key Features:**
- Kanban board interface
- Drag-and-drop status updates
- Task assignment
- Integrated chat discussions
- Due date tracking

---

#### 7. Chatbots
**Route:** `/main/[organizationId]/website-chat`  
**Menu Label:** Chatbots  
**Icon:** Bot  
**Visibility:** Hidden for Client role users

**Purpose:**
Build and manage AI-powered chatbots for website customer support.

**How It Works:**
- Chatbot builder interface
- Configure chatbot responses
- Train chatbot with brand information
- Deploy chatbots to websites
- Monitor chatbot interactions

**Key Features:**
- Visual chatbot builder
- AI training integration
- Website deployment
- Analytics and monitoring

---

#### 8. Landing Pages
**Route:** `/main/[organizationId]/landing-pages`  
**Menu Label:** Landing Pages  
**Icon:** Layout  
**Visibility:** Hidden for Client role users

**Purpose:**
Create and manage landing pages for marketing campaigns.

**How It Works:**
- Landing page builder
- Template library
- Page editing interface
- Preview functionality
- Publishing to domains

**Key Features:**
- Drag-and-drop page builder
- Responsive templates
- SEO optimization
- A/B testing support

---

#### 9. Review
**Route:** `/main/[organizationId]/review`  
**Menu Label:** Review  
**Icon:** FileCheck  
**Visibility:** Only shown when a brand is selected

**Purpose:**
Content approval workflow for reviewing and approving content before publication.

**How It Works:**
- **Content Table:** Lists all content items pending review:
  - Channels (platform badges)
  - Content preview (truncated)
  - Author name
  - Submission date
  - Status badge (Pending Review, Approved, Rejected)
  - View action button
- **Status Badges:**
  - Pending Review: Yellow badge with FileCheck icon
  - Approved: Green badge with ThumbsUp icon
  - Rejected: Red badge with ThumbsDown icon
- **Review Dialog:**
  - Full content display
  - Attachment preview (images/PDFs)
  - PDF annotation detection
  - Approve/Reject buttons
  - Comment functionality
- **Data Management:**
  - Loads from placeholder data and localStorage
  - Filters out content from inactive campaigns
  - Persists status changes to localStorage
  - Key: `reviewContent_${organizationId}`
- **Workflow:**
  1. Content generated from campaigns
  2. Submitted for review automatically
  3. Reviewers approve/reject
  4. Authors notified (simulated)
  5. Approved content ready for publishing

**Key Features:**
- Comprehensive content review table
- Status tracking
- PDF annotation support
- Email notifications (simulated)
- Email notifications

**Laravel 12 Considerations:**
- Create ReviewController with index, show, approve, and reject methods
- Implement ReviewService for review item retrieval and status management
- Use Review model with relationships to Organization, Campaign, User (author), and Channel
- Store review items in database instead of localStorage
- Implement ReviewPolicy for authorization (only reviewers can approve/reject)
- Use queue jobs to send email notifications when review status changes
- Create notification classes for review approval/rejection emails
- Implement PDF annotation detection using a service class or package
- Use database queries to filter reviews by campaign status (exclude inactive campaigns)
- Implement review workflow state management
- Use eager loading for author, campaign, and channel relationships
- Consider implementing review comments/feedback functionality
- Use database transactions when updating review status to ensure data consistency

---

#### 10. Files
**Route:** `/main/[organizationId]/files`  
**Menu Label:** Files  
**Icon:** Folder  
**Visibility:** Only shown when a brand is selected

**Purpose:**
File management system for storing and organizing brand assets, documents, and media files.

**How It Works:**
- File browser interface
- Upload functionality
- Folder organization
- File preview
- Search and filter
- File sharing

**Key Features:**
- Cloud storage integration
- File versioning
- Access control
- Media preview
- Bulk operations

---

#### 11. Analytics
**Route:** `/main/[organizationId]/analytics`  
**Menu Label:** Analytics  
**Icon:** LineChart  
**Visibility:** Only shown when a brand is selected

**Purpose:**
AI-powered campaign performance analysis and insights.

**How It Works:**
- **Analysis Form:** Left panel with:
  - Client Name input
  - Campaign Name dropdown (populated from scheduled posts)
  - Analyze button
- **Results Display:** Right panel showing:
  - Analysis Summary: AI-generated overview
  - Key Insights: Bullet points of important findings
  - Recommendations: Actionable suggestions
- **AI Processing:**
  - Uses `analyzeDataAction` server action
  - Processes campaign data with AI
  - Generates insights based on platform metrics
  - Returns structured analysis
- **Data Source:**
  - Campaigns from scheduled posts
  - Pre-filled platform data (Facebook, Instagram, LinkedIn)
  - Metrics: impressions, clicks, conversions

**Key Features:**
- AI-powered analysis
- Campaign selection
- Structured insights
- Actionable recommendations
- Performance metrics

**Laravel 12 Considerations:**
- Create AnalyticsController with analyze method that accepts campaign selection
- Implement AnalyticsService to handle AI processing and data analysis
- Use queue jobs for AI analysis to avoid blocking the request
- Store analysis results in analytics_reports or analytics_metrics tables
- Integrate with AI service (Genkit flows) using service classes
- Use Form Request for validating campaign selection input
- Implement caching for frequently accessed campaign metrics
- Create API endpoints if frontend needs to fetch analysis results asynchronously
- Use database relationships to fetch campaign data and scheduled posts
- Implement proper error handling for AI service failures
- Consider storing analysis history for comparison over time
- Use database transactions when saving analysis results

---

#### 12. Brands
**Route:** `/main/[organizationId]/brands`  
**Menu Label:** Brands  
**Icon:** Shapes  
**Visibility:** Shown when no brand is selected

**Purpose:**
Create and manage brand profiles that define brand identity for AI-generated content.

**How It Works:**
- **Brand List:** Table/grid view showing:
  - Brand name
  - Brand summary
  - Brand guidelines preview
  - Actions (edit, delete)
- **Brand Creation:**
  - Brand wizard/form
  - Name and summary
  - Detailed brand guidelines
  - Tone of voice settings
  - Target audience information
  - Keywords to use/avoid
- **Brand View Component:**
  - Displays all brands for organization
  - Add New Brand button
  - Brand editing interface
  - Brand deletion
- **AI Integration:**
  - Brand information used by AI tools
  - Ensures consistent content generation
  - Brand-specific content creation
- **Visual Ideation:**
  - Toggle for "AI-Generated Concept Brand"
  - Visual brand ideation tool
  - Brand asset generation

**Key Features:**
- Brand profile management
- Brand guidelines editor
- AI training data
- Visual ideation tools
- Brand asset management

---

#### 13. Channels
**Route:** `/main/[organizationId]/channels`  
**Menu Label:** Channels  
**Icon:** Radio  
**Visibility:** Shown when no brand is selected

**Purpose:**
Configure and manage social media channels and communication platforms.

**How It Works:**
- Channel list display
- Add/edit channel configurations
- Platform-specific settings
- API key management
- Connection status
- Channel testing

**Key Features:**
- Multi-platform support
- API integration
- Connection management
- Channel testing tools

---

#### 14. Products
**Route:** `/main/[organizationId]/products`  
**Menu Label:** Products  
**Icon:** Boxes  
**Visibility:** Shown when no brand is selected

**Purpose:**
Manage product catalog for use in marketing campaigns and content generation.

**How It Works:**
- **Product View:** Main display area showing:
  - Product grid/list
  - Product cards with images
  - Product details
  - Categories sidebar
- **Product Management:**
  - Add new products
  - Edit existing products
  - Product import (CSV/Excel)
  - Bulk operations
- **Categories:**
  - Category management
  - Product categorization
  - Category filtering
- **Product Form:**
  - Name, description, price
  - Images upload
  - Category assignment
  - SKU and inventory
  - Product variants

**Key Features:**
- Product catalog management
- Category organization
- Bulk import
- Image management
- Inventory tracking

---

#### 15. Contacts
**Route:** `/main/[organizationId]/contacts`  
**Menu Label:** Contacts  
**Icon:** Contact  
**Visibility:** Shown when no brand is selected

**Purpose:**
Manage contact database for email marketing, customer relationship management, and communication.

**How It Works:**
- Contact list/table
- Contact import (CSV)
- Contact groups/segments
- Contact details view
- Email integration
- Contact search and filter

**Key Features:**
- Contact database
- Import/export functionality
- Segmentation
- Email integration
- Contact history

---

#### 16. Email Marketing
**Route:** `/main/[organizationId]/email`  
**Menu Label:** Email Campaigns  
**Icon:** Mail  
**Visibility:** Shown when no brand is selected

**Purpose:**
Create and manage email marketing campaigns.

**How It Works:**
- Email campaign list
- Campaign creation wizard
- Email template editor
- Contact list selection
- Scheduling
- Performance tracking

**Key Features:**
- Email campaign builder
- Template library
- Contact segmentation
- Scheduling
- Analytics

---

#### 17. Surveys
**Route:** `/main/[organizationId]/surveys`  
**Menu Label:** Surveys  
**Icon:** FileCheck  
**Visibility:** Shown when no brand is selected

**Purpose:**
Create and manage surveys for customer feedback and market research.

**How It Works:**
- Survey list
- Survey builder
- Question types
- Survey distribution
- Response collection
- Analytics

**Key Features:**
- Visual survey builder
- Multiple question types
- Distribution management
- Response analytics

---

#### 18. Paid Ads - Ad Campaigns
**Route:** `/main/[organizationId]/paid-ads/campaigns`  
**Menu Label:** Ad Campaigns  
**Icon:** FilePieChart  
**Visibility:** Shown when no brand is selected

**Purpose:**
Manage paid advertising campaigns across platforms like Google Ads, Facebook Ads, etc.

**How It Works:**
- Ad campaign dashboard
- Campaign creation
- Budget management
- Performance tracking
- Platform integration

**Key Features:**
- Multi-platform ad management
- Budget tracking
- Performance analytics
- Ad scheduling

---

#### 19. Paid Ads - Ad Copy Generator
**Route:** `/main/[organizationId]/paid-ads/ad-copy`  
**Menu Label:** Ad Copy Generator  
**Icon:** PenSquare  
**Visibility:** Shown when no brand is selected

**Purpose:**
AI-powered tool for generating ad copy for paid advertising campaigns.

**How It Works:**
- Ad copy generation form
- Platform selection
- Target audience input
- AI generation
- Copy variations
- Export functionality

**Key Features:**
- AI-powered generation
- Multiple platform support
- Copy variations
- Brand consistency

---

#### 20. Paid Ads - Keyword Research
**Route:** `/main/[organizationId]/paid-ads/keyword-research`  
**Menu Label:** Keyword Research  
**Icon:** Search  
**Visibility:** Shown when no brand is selected

**Purpose:**
Research and analyze keywords for SEO and paid advertising campaigns.

**How It Works:**
- Keyword search tool
- Keyword suggestions
- Search volume data
- Competition analysis
- Keyword grouping
- Export keywords

**Key Features:**
- Keyword discovery
- Search volume metrics
- Competition analysis
- Keyword organization

---

#### 21. Content Ideation - SEO Analysis
**Route:** `/main/[organizationId]/tools/seo-analysis`  
**Menu Label:** SEO Analysis  
**Icon:** Globe  
**Visibility:** Shown when no brand is selected

**Purpose:**
Analyze website SEO performance and get recommendations for improvement.

**How It Works:**
- Website URL input
- SEO analysis execution
- Report generation
- Recommendations display
- Technical SEO checks

**Key Features:**
- Website SEO scanning
- Technical SEO analysis
- Content recommendations
- Performance metrics

---

#### 22. Content Ideation - Email Template
**Route:** `/main/[organizationId]/tools/email-template`  
**Menu Label:** Email Template  
**Icon:** Mail  
**Visibility:** Shown when no brand is selected

**Purpose:**
Generate email templates using AI for marketing campaigns.

**How It Works:**
- Template generation form
- Purpose selection
- Brand context input
- AI generation
- Template preview
- Export/save template

**Key Features:**
- AI template generation
- Brand-aware templates
- Multiple email types
- Template customization

---

#### 23. Content Ideation - Label Inspiration
**Route:** `/main/[organizationId]/tools/label-inspiration`  
**Menu Label:** Label Inspiration  
**Icon:** Tags  
**Visibility:** Shown when no brand is selected

**Purpose:**
Generate creative label ideas and taglines for products and campaigns.

**How It Works:**
- Product/brand input
- Label generation
- Multiple variations
- Inspiration gallery
- Export options

**Key Features:**
- Creative label generation
- Multiple variations
- Brand consistency
- Export functionality

---

#### 24. Content Ideation - Image Generator
**Route:** `/main/[organizationId]/tools/image-generator`  
**Menu Label:** Image Generator  
**Icon:** ImageIcon  
**Visibility:** Shown when no brand is selected

**Purpose:**
Generate images using AI for marketing materials and campaigns.

**How It Works:**
- Image prompt input
- Style selection
- Size options
- AI generation
- Image preview
- Download functionality

**Key Features:**
- AI image generation
- Style customization
- Multiple formats
- Brand-aligned images

---

#### 25. Content Ideation - Product Catalog
**Route:** `/main/[organizationId]/tools/product-catalog`  
**Menu Label:** Product Catalog  
**Icon:** BookCopy  
**Visibility:** Shown when no brand is selected

**Purpose:**
Generate product catalog content and descriptions using AI.

**How It Works:**
- Product selection
- Catalog generation
- Description creation
- Formatting options
- Export catalog

**Key Features:**
- Bulk catalog generation
- Product descriptions
- Format customization
- Export options

---

#### 26. Intelligence - Sentiment Analysis
**Route:** `/main/[organizationId]/sentiment`  
**Menu Label:** Sentiment Analysis  
**Icon:** Smile  
**Visibility:** Shown when no brand is selected

**Purpose:**
Analyze sentiment of social media mentions, reviews, and customer feedback.

**How It Works:**
- Text input or URL
- Sentiment analysis execution
- Sentiment score display
- Positive/negative/neutral classification
- Keyword extraction
- Trends over time

**Key Features:**
- Real-time sentiment analysis
- Multi-source analysis
- Trend tracking
- Keyword insights

---

#### 27. Intelligence - Predictive Analytics
**Route:** `/main/[organizationId]/predictive`  
**Menu Label:** Predictive Analytics  
**Icon:** TrendingUp  
**Visibility:** Shown when no brand is selected

**Purpose:**
Predict future campaign performance and marketing trends using AI.

**How It Works:**
- Campaign selection
- Historical data input
- Prediction model execution
- Forecast display
- Confidence intervals
- Recommendations

**Key Features:**
- Performance prediction
- Trend forecasting
- Risk assessment
- Optimization suggestions

---

#### 28. Intelligence - Competitor Analysis
**Route:** `/main/[organizationId]/competitor-analysis`  
**Menu Label:** Competitor Analysis  
**Icon:** Target  
**Visibility:** Shown when no brand is selected

**Purpose:**
Analyze competitor strategies, content, and performance.

**How It Works:**
- Competitor identification
- Data collection
- Analysis execution
- Comparison reports
- Strategy insights
- Gap analysis

**Key Features:**
- Competitor tracking
- Performance comparison
- Strategy insights
- Gap identification

---

#### 29. Organization - Settings
**Route:** `/main/[organizationId]/settings`  
**Menu Label:** Settings  
**Icon:** Settings  
**Visibility:** Only for Organization Admins

**Purpose:**
Configure organization-wide settings, preferences, and integrations.

**How It Works:**
- Settings tabs/sections
- General settings
- Integrations
- User preferences
- Notification settings
- Security settings

**Key Features:**
- Organization configuration
- Integration management
- User preferences
- Security controls

---

#### 30. Organization - Billing
**Route:** `/main/[organizationId]/billing`  
**Menu Label:** Billing  
**Icon:** CreditCard  
**Visibility:** Only for Organization Admins

**Purpose:**
Manage subscription, payment methods, invoices, and billing history.

**How It Works:**
- Subscription plan display
- Payment method management
- Invoice history
- Usage tracking
- Billing alerts

**Key Features:**
- Subscription management
- Payment processing
- Invoice access
- Usage monitoring

---

#### 31. Organization - Team Members
**Route:** `/main/[organizationId]/team`  
**Menu Label:** Team Members  
**Icon:** Users  
**Visibility:** Only for Organization Admins

**Purpose:**
Manage team members, roles, permissions, and invitations.

**How It Works:**
- Team member list
- Add/remove members
- Role assignment
- Permission management
- Invitation system

**Key Features:**
- User management
- Role-based access
- Invitation system
- Permission controls

---

#### 32. Organization - Storage Sources
**Route:** `/main/[organizationId]/storage-sources`  
**Menu Label:** Storage Sources  
**Icon:** CloudCog  
**Visibility:** Only for Organization Admins

**Purpose:**
Configure cloud storage integrations for file management.

**How It Works:**
- Storage provider list
- Connection setup
- Authentication
- Storage configuration
- Sync settings

**Key Features:**
- Multi-provider support
- Secure connections
- Sync management
- Storage quotas

---

#### 33. Organization - Automations
**Route:** `/main/[organizationId]/automations`  
**Menu Label:** Automations  
**Icon:** Workflow  
**Visibility:** Only for Organization Admins

**Purpose:**
Create and manage automated workflows for marketing tasks.

**How It Works:**
- Automation list
- Workflow builder
- Trigger configuration
- Action setup
- Testing and activation

**Key Features:**
- Visual workflow builder
- Multiple triggers
- Action library
- Testing tools

---

## AGENCY PANEL

The Agency Panel is accessed via `/agency/[agencyId]/*` routes and provides tools for agencies managing multiple client organizations.

### Main Menu Structure

#### 1. Clients
**Route:** `/agency/[agencyId]/clients`  
**Menu Label:** Clients  
**Icon:** Building2  
**Default Route:** Redirects from `/agency/[agencyId]`

**Purpose:**
View and manage all client organizations associated with the agency.

**How It Works:**
- **Client Table:** Displays:
  - Client (Organization) name
  - Total users count
  - Action button (View/Manage)
- **Client Row Component:**
  - Organization name display
  - User count from team members
  - "View Organization" button linking to client's organization view
- **Add New Client:**
  - Button linking to onboarding wizard
  - Creates new organization
  - Associates with agency
- **Data Source:**
  - Fetches clients via `getAgencyClientsAction`
  - Filters organizations by agencyId
  - Shows client organizations only

**Key Features:**
- Client organization overview
- Quick access to client dashboards
- Client onboarding integration
- User count tracking

**Laravel 12 Considerations:**
- Create AgencyClientController with index and show methods
- Implement AgencyService to retrieve clients associated with the agency
- Use Agency model relationship to clients (many-to-many through agency_clients table)
- Use eager loading with count for user counts to optimize queries
- Implement AgencyPolicy to ensure users can only access their agency's clients
- Use route model binding for agency and organization parameters
- Consider implementing client search and filtering functionality
- Use database queries instead of placeholder data
- Implement proper authorization to ensure agency members can only view their agency's clients
- Consider adding pagination for large client lists
- Use caching for frequently accessed client data

---

#### 2. Tasks
**Route:** `/agency/[agencyId]/tasks`  
**Menu Label:** Tasks  
**Icon:** ClipboardList

**Purpose:**
Manage tasks across all agency clients in a unified Kanban board.

**How It Works:**
- **Unified Kanban Board:** Same structure as organization tasks but shows:
  - Tasks from all client organizations
  - Client badge on each task card
  - Client name displayed
- **Task Cards:**
  - Client badge (secondary badge)
  - Task title
  - Due date
  - Assignee avatar
  - Edit and more options
- **Task Details Dialog:**
  - Shows client name in description
  - Full task details
  - Discussion panel
- **Task Management:**
  - Create tasks for any client
  - Assign to agency team members
  - Drag-and-drop status updates
  - Cross-client task visibility
- **Data:** Shows all tasks, filtered by agency's client organizations

**Key Features:**
- Cross-client task management
- Client identification on tasks
- Unified workflow
- Agency team assignment

---

#### 3. Aggregated Calendar
**Route:** `/agency/[agencyId]/calendar`  
**Menu Label:** Aggregated Calendar  
**Icon:** Calendar

**Purpose:**
View all scheduled content and events from all client organizations in a unified calendar view.

**How It Works:**
- **Full Calendar Component:**
  - Calendar view (month/week/day)
  - All client events displayed
  - Color coding by client
  - Event details on click
- **Data Aggregation:**
  - Fetches all client organization IDs for agency
  - Filters scheduled posts from all clients
  - Combines into single calendar
- **Event Display:**
  - Scheduled posts from all clients
  - Campaign launches
  - Content publication dates
  - Client identification
- **Interactions:**
  - Click events for details
  - Navigate between dates
  - Filter by client (potential)

**Key Features:**
- Multi-client calendar view
- Unified scheduling overview
- Event aggregation
- Client identification
- Calendar navigation

---

#### 4. Billing & Invoicing
**Route:** `/agency/[agencyId]/billing`  
**Menu Label:** Billing & Invoicing  
**Icon:** CreditCard

**Purpose:**
Manage invoices, track payments, and monitor billing across all agency clients.

**How It Works:**
- **Summary Cards:**
  - Total Billed (This Year): Sum of all paid invoices
  - Pending Payments: Sum of pending invoices
  - Overdue Payments: Sum of overdue invoices
  - Automation Card: Automated reminder settings
- **Invoice Table:**
  - Invoice ID (monospace)
  - Organization name
  - Amount
  - Status badge (Paid/Pending/Overdue)
  - Issue date
  - Due date
  - Actions (Download, Pay Now)
- **Status Badges:**
  - Paid: Green badge
  - Pending: Yellow badge
  - Overdue: Red badge
- **Actions:**
  - Download invoice PDF
  - Pay invoice (marks as paid)
  - Automated reminders toggle
  - Run reminder check manually
- **Automation:**
  - Toggle for automated reminders
  - Manual reminder check button
  - Uses `sendInvoiceRemindersAction`
- **Data:** Invoice data from `initialInvoices` placeholder data

**Key Features:**
- Multi-client invoice management
- Payment tracking
- Automated reminders
- Invoice download
- Status monitoring

**Laravel 12 Considerations:**
- Create AgencyBillingController with index, pay, download, and sendReminders methods
- Implement InvoiceService for invoice management and summary calculations
- Use Invoice model with relationships to Organization and Subscription
- Implement queue job (SendInvoiceReminders) for automated reminder processing
- Use scheduled tasks (Laravel Scheduler) to run reminder checks automatically
- Create notification classes for invoice reminders
- Implement PDF generation service for invoice downloads
- Use database queries to calculate summary statistics (total billed, pending, overdue)
- Implement proper authorization to ensure only agency admins can manage billing
- Use database transactions when marking invoices as paid
- Consider implementing payment gateway integration for actual payment processing
- Store invoice status changes in activity logs for audit trail
- Use eager loading for organization relationships when displaying invoices

---

#### 5. Reporting
**Route:** `/agency/[agencyId]/reports`  
**Menu Label:** Reporting  
**Icon:** FileText

**Purpose:**
Generate AI-powered performance reports for agency clients.

**How It Works:**
- **Report Generation Form:** Left panel:
  - Client dropdown (populated from agency clients)
  - Report Type dropdown (Weekly/Monthly/Quarterly)
  - Generate Report button
- **Report Display:** Right panel showing:
  - Executive Summary: High-level overview
  - Key Metrics: Grid of metrics with values and changes
  - Highlights: Positive achievements (green)
  - Recommendations: Improvement suggestions (yellow)
  - Download as PDF button
- **AI Processing:**
  - Uses `generateAgencyReportAction`
  - Analyzes client data
  - Generates structured report
  - Provides insights and recommendations
- **Report Structure:**
  - Executive summary paragraph
  - Key metrics with change indicators
  - Bulleted highlights
  - Bulleted recommendations
- **Loading States:**
  - Loading spinner during generation
  - Success message on completion
  - Error handling

**Key Features:**
- AI-powered report generation
- Client selection
- Multiple report types
- Structured insights
- PDF export
- Performance metrics

---

#### 6. Team Management
**Route:** `/agency/[agencyId]/team`  
**Menu Label:** Team Management  
**Icon:** Users

**Purpose:**
Manage agency team members, roles, and permissions across client work.

**How It Works:**
- Team member list
- Add/remove team members
- Role assignment
- Client access permissions
- Agency-wide permissions

**Key Features:**
- Agency team management
- Role-based access
- Client assignment
- Permission controls

---

#### 7. Agency Settings
**Route:** `/agency/[agencyId]/settings`  
**Menu Label:** Agency Settings  
**Icon:** Settings

**Purpose:**
Configure agency-wide settings, branding, and preferences.

**How It Works:**
- Agency profile settings
- Branding configuration
- Default settings
- Integration management
- Notification preferences

**Key Features:**
- Agency configuration
- Branding management
- Default settings
- Integration setup

---

## COMMON PATTERNS AND ARCHITECTURE

### Data Storage
- **localStorage Keys:**
  - `reviewContent_${organizationId}`: Review items
  - `campaign_statuses_${organizationId}`: Campaign statuses
  - `basis_${campaignId}`: Campaign draft data
  - `plan_${campaignId}`: Campaign plan data

### Navigation
- Organization context: `/main/[organizationId]/*`
- Agency context: `/agency/[agencyId]/*`
- Brand context: `?brandId=${brandId}` query parameter

### Role-Based Access

**Customer Panel Roles:**
- **Client Role:** Limited view, no campaign creation, read-only access to most resources
- **Admin Role (Organization Admin):** Full access to organization settings, can create/manage all resources

**Agency Panel Roles:**
- **Agency Member:** Standard access to clients, tasks, calendar, and reporting
- **Agency Admin:** Full agency access including billing, team management, and settings

**Admin Panel Roles:**
- **Admin (Platform Admin):** Complete platform administration with unrestricted access

**Cross-Panel Access:**
- Users with `user_type = 'admin'` can access Admin Panel via `/admin/*` routes
- Users with agency membership can access Agency Panel via `/agency/[agencyId]/*` routes
- Organization members access Customer Panel via `/main/[organizationId]/*` routes
- Role assignments are context-specific (organization-level, agency-level, platform-level)

**Detailed Permission Documentation:**
- **Customer Panel:** See "Role-Based Permissions and Visibility" section under Customer Panel UI Structure (includes permission matrix)
- **Agency Panel:** See "Role-Based Permissions and Visibility" section under Agency Panel UI Structure (includes permission matrix)
- **Admin Panel:** See "Role-Based Permissions and Visibility" section under Admin Panel UI Structure (includes permission matrix)

### Component Reusability
- Shared UI components from `@/components/ui/*`
- Reusable business components (ChatPanel, BrandView, etc.)
- Consistent styling with Tailwind CSS

### AI Integration
- Server actions for AI operations
- Genkit flows for AI processing
- Structured data schemas for AI responses

---

## LARAVEL 12 ARCHITECTURAL GUIDELINES

### General Implementation Patterns

**Controllers:**
- Keep controllers thin - delegate business logic to service classes
- Use dependency injection for services
- Return proper HTTP responses (views, JSON, redirects)
- Use route model binding for resource parameters
- Implement proper authorization checks using policies

**Services:**
- Create service classes for complex business logic
- Use service classes to encapsulate database operations
- Implement service methods that return models or collections
- Handle business rules and validations in services
- Use dependency injection for service dependencies

**Models:**
- Define relationships as documented in MODEL_RELATIONSHIPS.md
- Use Eloquent accessors and mutators for data transformation
- Implement query scopes for reusable query logic
- Use fillable/guarded properties for mass assignment protection
- Implement soft deletes where appropriate

**Form Requests:**
- Use Form Request classes for validation
- Implement authorization logic in authorize() method
- Define validation rules in rules() method
- Return custom error messages in messages() method

**Policies:**
- Create policy classes for resource authorization
- Implement viewAny, view, create, update, delete methods
- Use policies in controllers via authorize() method
- Consider organization/agency context in policy checks

**Middleware:**
- Create middleware for organization and agency access control
- Use middleware groups for route organization
- Implement proper error responses for unauthorized access

**Queue Jobs:**
- Use queue jobs for AI processing, email sending, and long-running tasks
- Implement proper error handling and retry logic
- Use job batching for bulk operations
- Monitor failed jobs and implement retry strategies

**Events & Listeners:**
- Create events for important actions (campaign published, review approved, etc.)
- Implement listeners for side effects (notifications, logging, etc.)
- Use event broadcasting for real-time updates if needed

**Database:**
- Use migrations for schema changes
- Implement proper indexes for performance
- Use database transactions for complex operations
- Consider using database views for complex queries

**Caching:**
- Implement caching for expensive queries
- Use cache tags for grouped cache invalidation
- Cache frequently accessed data (brands, channels, etc.)
- Implement cache warming strategies

**Testing:**
- Write feature tests for routes and controllers
- Write unit tests for services and models
- Use factories for test data generation
- Test authorization policies
- Test queue jobs and event listeners

---

## NOTES FOR DEVELOPERS

**TODO:** Review and update all developer notes to ensure they reflect Blade Templates + Alpine.js + Vue.js approach and remove any remaining Livewire references.

1. **State Management:** Replace React localStorage with Laravel database persistence. Use Vue.js components for complex stateful UI or API endpoints with proper state management. Use Alpine.js for simple local state.

2. **Data Fetching:** Replace placeholder data with Eloquent queries. Use Blade templates for server-side rendering, or implement API endpoints for Vue.js components that need dynamic data fetching.

3. **Authentication:** Use Laravel's built-in authentication system. Implement proper authorization using policies and middleware.

4. **Error Handling:** Use Laravel's exception handling. Return proper HTTP status codes and error messages. Implement user-friendly error pages.

5. **Loading States:** Use Alpine.js for simple loading states in Blade templates. For Vue.js components, implement loading states within the component. For API endpoints, return proper loading indicators and handle them in Vue components.

6. **Responsive Design:** Use Tailwind CSS classes. Ensure Blade components are responsive. Test on mobile devices.

7. **Accessibility:** Add proper ARIA labels in Blade templates. Use semantic HTML. Ensure keyboard navigation works.

8. **Testing:** Write comprehensive tests using PHPUnit. Test routes, controllers, services, policies, and queue jobs.

9. **Performance:** Use eager loading to prevent N+1 queries. Implement caching for expensive operations. Use database indexes. Implement pagination for large datasets.

10. **Internationalization:** Use Laravel's localization features. Store translations in lang/ directory. Use translation helpers in Blade templates.


@php
    $organizationId = request()->route('organizationId');
    $brandId = request()->query('brandId');
    
    $organization = \App\Models\Organization::find($organizationId);
    $brands = $organization ? $organization->brands : collect();
    $hasBrands = $brands->count() > 0;
    $hasBrandSelected = !is_null($brandId);
    
    $user = auth()->user();
    $isClientRole = $organization && ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization));
    $isOrgAdmin = $organization && ($user->hasRole('admin', $organization) || $user->hasRole('super-admin', $organization));
    
    // Load review count from ContentApproval model
    $reviewCount = 0;
    if ($organization) {
        $reviewService = app(\App\Services\ReviewService::class);
        $reviewStats = $reviewService->getReviewStats($organizationId);
        $reviewCount = $reviewStats['pending'] ?? 0;
    }
    
    // Load mention count from ChatMessage (search for @username mentions)
    $mentionCount = 0;
    if ($organization && $user) {
        // Get user's name variations for mention detection
        $userNameParts = explode(' ', $user->name);
        $firstName = $userNameParts[0] ?? '';
        $lastName = $userNameParts[1] ?? '';
        
        // Get all chat topics for this organization where user is a participant
        $userTopics = \App\Models\ChatTopic::where('organization_id', $organizationId)
            ->whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');
        
        if ($userTopics->isNotEmpty()) {
            // Count unread messages that mention the user
            $mentionCount = \App\Models\ChatMessage::whereIn('chat_topic_id', $userTopics)
                ->where('user_id', '!=', $user->id) // Exclude own messages
                ->where(function($query) use ($user, $firstName, $lastName) {
                    // Check for @username mentions
                    $query->where('message', 'like', '%@' . str_replace(' ', '', $user->name) . '%')
                          ->orWhere('message', 'like', '%@' . $firstName . '%')
                          ->orWhere('message', 'like', '%@' . $lastName . '%');
                })
                ->whereDoesntHave('topic.participants', function($q) use ($user) {
                    // Exclude messages user has already read
                    $q->where('user_id', $user->id)
                      ->whereNotNull('last_read_at')
                      ->whereColumn('last_read_at', '>=', 'chat_messages.created_at');
                })
                ->count();
        }
    }
    
    // Icon helper
    $icon = function($name) {
        $icons = [
            'home' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>',
            'campaigns' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>',
            'projects' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>',
            'tasks' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>',
            'chatbots' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>',
            'landing-pages' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
            'brand-assets' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            'review' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'files' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>',
            'analytics' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>',
            'brands' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>',
            'channels' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>',
            'products' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
            'contacts' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
            'email-marketing' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
            'paid-ads' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>',
            'content-ideation' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>',
            'intelligence' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>',
            'organization' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>',
        ];
        return $icons[$name] ?? '';
    };
@endphp

<x-partials.layout.sidebar variant="default">
    <x-partials.layout.sidebar-header variant="default">
        <div class="flex items-center space-x-2 mb-4">
            <a href="{{ route('main.dashboard', ['organizationId' => $organizationId]) }}" class="flex items-center space-x-2">
                <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span class="text-xl font-bold text-gray-900">MarketPulse</span>
            </a>
        </div>
        @if($hasBrands)
            <x-partials.layout.brand-switcher 
                :brands="$brands" 
                :selectedBrandId="$brandId"
                :organizationId="$organizationId"
            />
        @endif
    </x-partials.layout.sidebar-header>

    <x-partials.layout.sidebar-content>
        <x-partials.layout.sidebar-menu>
            {{-- Always visible items --}}
            <x-partials.layout.sidebar-menu-item 
                href="{{ route('main.collaboration.index', ['organizationId' => $organizationId]) }}"
                :icon="$icon('home')"
                :badge="$mentionCount > 0 ? $mentionCount : null"
                badge-variant="danger"
                :is-active="request()->routeIs('main.collaboration.*')"
            >
                Home
            </x-partials.layout.sidebar-menu-item>

            {{-- Brand context items (only when brand selected) --}}
            @if($hasBrandSelected)
                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.brand-assets', ['organizationId' => $organizationId]) }}?brandId={{ $brandId }}"
                    :icon="$icon('brand-assets')"
                    :is-active="request()->routeIs('main.brand-assets')"
                >
                    Brand Assets
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.content-approvals.index', ['organizationId' => $organizationId]) }}?brandId={{ $brandId }}"
                    :icon="$icon('review')"
                    :badge="$reviewCount > 0 ? $reviewCount : null"
                    badge-variant="danger"
                    :is-active="request()->routeIs('main.content-approvals.*')"
                >
                    Review
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.files', ['organizationId' => $organizationId]) }}?brandId={{ $brandId }}"
                    :icon="$icon('files')"
                    :is-active="request()->routeIs('main.files')"
                >
                    Files
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.analytics', ['organizationId' => $organizationId]) }}?brandId={{ $brandId }}"
                    :icon="$icon('analytics')"
                    :is-active="request()->routeIs('main.analytics')"
                >
                    Analytics
                </x-partials.layout.sidebar-menu-item>
            @endif

            {{-- Role-based items (hidden for Client role) --}}
            @if(!$isClientRole)
                <x-partials.layout.sidebar-collapsible 
                    label="Campaigns"
                    :icon="$icon('campaigns')"
                    :default-open="request()->routeIs('main.campaigns.*')"
                >
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.campaigns.index', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.campaigns.index') || request()->routeIs('main.campaigns.show')"
                    >
                        Campaigns
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.campaigns.competitions.index', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.campaigns.competitions.*')"
                    >
                        Competitions
                    </x-partials.layout.sidebar-menu-item>
                </x-partials.layout.sidebar-collapsible>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.projects', ['organizationId' => $organizationId]) }}"
                    :icon="$icon('projects')"
                    :is-active="request()->routeIs('main.projects.*')"
                >
                    Projects
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.tasks', ['organizationId' => $organizationId]) }}"
                    :icon="$icon('tasks')"
                    :is-active="request()->routeIs('main.tasks.*')"
                >
                    Tasks
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.chatbots.index', ['organizationId' => $organizationId]) }}"
                    :icon="$icon('chatbots')"
                    :is-active="request()->routeIs('main.chatbots.*')"
                >
                    Chatbots
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.landing-pages.index', ['organizationId' => $organizationId]) }}"
                    :icon="$icon('landing-pages')"
                    :is-active="request()->routeIs('main.landing-pages.*')"
                >
                    Landing Pages
                </x-partials.layout.sidebar-menu-item>
            @endif

            {{-- No brand context items (only when no brand selected) --}}
            @if(!$hasBrandSelected)
                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.brands.index', ['organizationId' => $organizationId]) }}"
                    :icon="$icon('brands')"
                    :is-active="request()->routeIs('main.brands.*')"
                >
                    Brands
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.social.channels.index', ['organizationId' => $organizationId]) }}"
                    :icon="$icon('channels')"
                    :is-active="request()->routeIs('main.social.channels.*')"
                >
                    Channels
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.products.index', ['organizationId' => $organizationId]) }}"
                    :icon="$icon('products')"
                    :is-active="request()->routeIs('main.products.*')"
                >
                    Products
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('main.email-marketing.contacts.index', ['organizationId' => $organizationId]) }}"
                    :icon="$icon('contacts')"
                    :is-active="request()->routeIs('main.email-marketing.contacts.*')"
                >
                    Contacts
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-collapsible 
                    label="Email Marketing"
                    :icon="$icon('email-marketing')"
                    :default-open="request()->routeIs('main.email-marketing.*')"
                >
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.email-marketing.campaigns.index', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.email-marketing.campaigns.*')"
                    >
                        Email Campaigns
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.surveys.index', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.surveys.*')"
                    >
                        Surveys
                    </x-partials.layout.sidebar-menu-item>
                </x-partials.layout.sidebar-collapsible>

                <x-partials.layout.sidebar-collapsible 
                    label="Paid Ads"
                    :icon="$icon('paid-ads')"
                    :default-open="request()->routeIs('main.paid-campaigns.*') || request()->routeIs('main.ai.ad-copy') || request()->routeIs('main.ai.keyword-research')"
                >
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.paid-campaigns.index', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.paid-campaigns.*')"
                    >
                        Ad Campaigns
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.ai.ad-copy', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.ai.ad-copy')"
                    >
                        Ad Copy Gen
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.ai.keyword-research', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.ai.keyword-research')"
                    >
                        Keyword Res
                    </x-partials.layout.sidebar-menu-item>
                </x-partials.layout.sidebar-collapsible>

                <x-partials.layout.sidebar-collapsible 
                    label="Content Ideation"
                    :icon="$icon('content-ideation')"
                    :default-open="request()->routeIs('main.tools.*')"
                >
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.tools.seo-analysis', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.tools.seo-analysis')"
                    >
                        SEO Analysis
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.tools.email-template', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.tools.email-template')"
                    >
                        Email Template
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.tools.label-inspiration', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.tools.label-inspiration')"
                    >
                        Label Insp
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.tools.image-generator', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.tools.image-generator')"
                    >
                        Image Gen
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.tools.product-catalog', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.tools.product-catalog')"
                    >
                        Product Cat
                    </x-partials.layout.sidebar-menu-item>
                </x-partials.layout.sidebar-collapsible>

                <x-partials.layout.sidebar-collapsible 
                    label="Intelligence"
                    :icon="$icon('intelligence')"
                    :default-open="request()->routeIs('main.intelligence.*') || request()->routeIs('main.competitors.*')"
                >
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.intelligence.sentiment', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.intelligence.sentiment')"
                    >
                        Sentiment
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.intelligence.predictive', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.intelligence.predictive')"
                    >
                        Predictive
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.competitors.index', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.competitors.*')"
                    >
                        Competitor
                    </x-partials.layout.sidebar-menu-item>
                </x-partials.layout.sidebar-collapsible>
            @endif

            {{-- Admin-only items --}}
            @if($isOrgAdmin)
                <x-partials.layout.sidebar-collapsible 
                    label="Organization"
                    :icon="$icon('organization')"
                    :default-open="request()->routeIs('main.settings') || request()->routeIs('main.billing') || request()->routeIs('main.team.*') || request()->routeIs('main.storage-sources.*') || request()->routeIs('main.automations.*')"
                >
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.settings', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.settings')"
                    >
                        Settings
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.billing', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.billing')"
                    >
                        Billing
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.team', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.team.*')"
                    >
                        Team Members
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.storage-sources', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.storage-sources.*')"
                    >
                        Storage Sources
                    </x-partials.layout.sidebar-menu-item>
                    <x-partials.layout.sidebar-menu-item 
                        href="{{ route('main.automations', ['organizationId' => $organizationId]) }}"
                        :is-active="request()->routeIs('main.automations.*')"
                    >
                        Automations
                    </x-partials.layout.sidebar-menu-item>
                </x-partials.layout.sidebar-collapsible>
            @endif
        </x-partials.layout.sidebar-menu>
    </x-partials.layout.sidebar-content>

    <x-partials.layout.sidebar-footer variant="default">
        <div class="flex items-center justify-between mb-2">
            <button
                x-on:click="$parent.collapsed = !$parent.collapsed"
                class="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                aria-label="Toggle sidebar"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>
        <x-partials.layout.user-menu variant="default" />
    </x-partials.layout.sidebar-footer>
</x-partials.layout.sidebar>


<?php

namespace App\Http\Controllers\Examples;

use App\Http\Controllers\Controller;
use App\Services\Localization\LocaleService;
use Illuminate\Http\Request;

/**
 * Example controller demonstrating localization features
 * This is for reference - not part of the actual application
 */
class LocalizationExampleController extends Controller
{
    protected LocaleService $localeService;

    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }

    /**
     * Example 1: Basic locale usage in controller
     */
    public function basicExample()
    {
        $currentLocale = current_locale(); // 'en'
        $regionalLocale = current_regional_locale(); // 'en_US'
        
        return view('examples.locale-basic', compact('currentLocale', 'regionalLocale'));
    }

    /**
     * Example 2: Formatting numbers and currency
     */
    public function formattingExample()
    {
        $data = [
            'product' => [
                'name' => 'Premium Widget',
                'price' => 1299.99,
                'quantity' => 1250,
                'rating' => 4.85,
            ],
            'formatted' => [
                'price' => format_currency(1299.99),           // $1,299.99
                'quantity' => format_number(1250, 0),          // 1,250
                'rating' => format_number(4.85, 2),            // 4.85
                'total' => format_currency(1299.99 * 1250),    // $1,624,987.50
            ],
        ];
        
        return view('examples.formatting', $data);
    }

    /**
     * Example 3: Using the service directly
     */
    public function serviceExample()
    {
        // Get locale information
        $localeDetails = $this->localeService->getLocaleDetails('en');
        $regionalVariants = $this->localeService->getRegionalVariants('en');
        $currency = $this->localeService->getCurrencyForRegionalLocale('en_US');
        
        // Format values
        $formattedNumber = $this->localeService->formatNumber(1234.56, 2);
        $formattedCurrency = $this->localeService->formatCurrency(99.99, 'USD');
        
        return view('examples.service', compact(
            'localeDetails',
            'regionalVariants',
            'currency',
            'formattedNumber',
            'formattedCurrency'
        ));
    }

    /**
     * Example 4: API response with localized data
     */
    public function apiExample()
    {
        $campaigns = [
            [
                'id' => 1,
                'name' => 'Summer Sale',
                'budget' => 5000.00,
                'impressions' => 125000,
                'clicks' => 2500,
            ],
            [
                'id' => 2,
                'name' => 'Winter Campaign',
                'budget' => 7500.00,
                'impressions' => 180000,
                'clicks' => 3200,
            ],
        ];
        
        // Format data for current locale
        $localized = array_map(function ($campaign) {
            return [
                'id' => $campaign['id'],
                'name' => $campaign['name'],
                'budget' => [
                    'raw' => $campaign['budget'],
                    'formatted' => format_currency($campaign['budget']),
                ],
                'impressions' => [
                    'raw' => $campaign['impressions'],
                    'formatted' => format_number($campaign['impressions'], 0),
                ],
                'clicks' => [
                    'raw' => $campaign['clicks'],
                    'formatted' => format_number($campaign['clicks'], 0),
                ],
                'ctr' => [
                    'raw' => ($campaign['clicks'] / $campaign['impressions']) * 100,
                    'formatted' => format_number(($campaign['clicks'] / $campaign['impressions']) * 100, 2) . '%',
                ],
            ];
        }, $campaigns);
        
        return response()->json([
            'data' => $localized,
            'meta' => [
                'locale' => current_locale(),
                'regional_locale' => current_regional_locale(),
                'currency' => get_currency_for_locale(),
            ],
        ]);
    }

    /**
     * Example 5: User preference management
     */
    public function userPreferenceExample(Request $request)
    {
        $user = $request->user();
        
        // Get user's preferred locale
        $userLocale = $user->getPreferredLocale();
        
        // Check if user has custom locale
        $hasCustom = $user->hasCustomLocale();
        
        // Set user's locale preference
        if ($request->has('locale')) {
            $newLocale = $request->input('locale');
            
            if ($this->localeService->isLocaleSupported($newLocale)) {
                $user->setPreferredLocale($newLocale);
                
                return redirect()->back()->with('success', __('locale.locale_saved'));
            }
        }
        
        return view('examples.user-preference', compact('userLocale', 'hasCustom'));
    }

    /**
     * Example 6: Organization locale settings
     */
    public function organizationExample($organizationId)
    {
        $organization = \App\Models\Organization::findOrFail($organizationId);
        
        // Get organization's locale info
        $orgLocale = $organization->getDefaultLocale();
        $supportedLocales = $organization->getSupportedLocales();
        
        // Check if specific locale is supported
        $supportsEnglish = $organization->supportsLocale('en');
        
        // Get all available locales for selection
        $availableLocales = enabled_locales();
        
        return view('examples.organization', compact(
            'organization',
            'orgLocale',
            'supportedLocales',
            'supportsEnglish',
            'availableLocales'
        ));
    }

    /**
     * Example 7: Translating with parameters
     */
    public function translationExample()
    {
        $userName = 'John Doe';
        $itemName = 'Campaign';
        
        $translations = [
            'simple' => __('common.welcome'),
            'with_param' => __('common.welcome', ['name' => config('app.name')]),
            'created' => __('common.created_successfully', ['item' => $itemName]),
            'pluralization' => trans_choice('campaign.campaigns', 1), // Campaign
            'plural' => trans_choice('campaign.campaigns', 2), // Campaigns
        ];
        
        return view('examples.translation', compact('translations'));
    }

    /**
     * Example 8: Conditional content based on locale
     */
    public function conditionalExample()
    {
        $locale = current_locale();
        $regionalLocale = current_regional_locale();
        
        // Different content based on region
        $greeting = match ($regionalLocale) {
            'en_US' => 'Hello!',
            'en_GB' => 'Hello!',
            'en_AU' => 'G\'day!',
            'en_CA' => 'Hello!',
            default => 'Hello!',
        };
        
        // Check text direction for styling
        $localeDetails = $this->localeService->getLocaleDetails($locale);
        $isRTL = ($localeDetails['direction'] ?? 'ltr') === 'rtl';
        
        return view('examples.conditional', compact('greeting', 'isRTL', 'regionalLocale'));
    }

    /**
     * Example 9: Caching localized data
     */
    public function cachingExample()
    {
        $locale = current_locale();
        
        // Cache key includes locale for per-locale caching
        $cacheKey = "products.featured.{$locale}";
        
        $products = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
            // This would fetch and format products
            return [
                [
                    'name' => __('products.widget'),
                    'price' => format_currency(99.99),
                ],
                [
                    'name' => __('products.gadget'),
                    'price' => format_currency(149.99),
                ],
            ];
        });
        
        return view('examples.caching', compact('products'));
    }

    /**
     * Example 10: Multi-locale form validation messages
     */
    public function validationExample(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'budget' => 'required|numeric|min:0',
        ]);
        
        // Validation messages automatically use current locale
        // Messages come from lang/{locale}/validation.php
        
        return redirect()->back()->with('success', __('common.saved_successfully', [
            'item' => __('campaign.campaign'),
        ]));
    }
}



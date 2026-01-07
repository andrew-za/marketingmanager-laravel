<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | List of all locales supported by the application.
    | Key is the locale code, value contains locale details.
    |
    */
    'supported_locales' => [
        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'script' => 'Latn',
            'regional' => [
                'en_US' => ['name' => 'English (United States)', 'currency' => 'USD', 'country_code' => 'US'],
                'en_GB' => ['name' => 'English (United Kingdom)', 'currency' => 'GBP', 'country_code' => 'GB'],
                'en_CA' => ['name' => 'English (Canada)', 'currency' => 'CAD', 'country_code' => 'CA'],
                'en_AU' => ['name' => 'English (Australia)', 'currency' => 'AUD', 'country_code' => 'AU'],
                'en_NZ' => ['name' => 'English (New Zealand)', 'currency' => 'NZD', 'country_code' => 'NZ'],
            ],
            'direction' => 'ltr',
            'enabled' => true,
        ],
        // Future locales can be added here
        // 'es' => [
        //     'name' => 'Spanish',
        //     'native_name' => 'Español',
        //     'script' => 'Latn',
        //     'regional' => [
        //         'es_ES' => ['name' => 'Spanish (Spain)', 'currency' => 'EUR', 'country_code' => 'ES'],
        //         'es_MX' => ['name' => 'Spanish (Mexico)', 'currency' => 'MXN', 'country_code' => 'MX'],
        //     ],
        //     'direction' => 'ltr',
        //     'enabled' => false,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale that will be used when no user preference is set.
    |
    */
    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Default Regional Locale
    |--------------------------------------------------------------------------
    |
    | The default regional locale (e.g., en_US) for formatting dates, 
    | numbers, and currency.
    |
    */
    'default_regional' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale to use when translations are missing.
    |
    */
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Locale Detection Order
    |--------------------------------------------------------------------------
    |
    | Order of priority for detecting user locale:
    | - user: User's saved preference
    | - session: Session-stored locale
    | - organization: Organization's default locale
    | - browser: Browser Accept-Language header
    | - default: Default locale from config
    |
    */
    'detection_order' => [
        'user',
        'session',
        'organization',
        'browser',
        'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    |
    | The session key used to store the current locale.
    |
    */
    'session_key' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | Regional Session Key
    |--------------------------------------------------------------------------
    |
    | The session key used to store the current regional locale.
    |
    */
    'regional_session_key' => 'regional_locale',

    /*
    |--------------------------------------------------------------------------
    | Hide Default Locale in URLs
    |--------------------------------------------------------------------------
    |
    | When true, the default locale will not be shown in URLs.
    | Example: /dashboard instead of /en/dashboard
    |
    */
    'hide_default_in_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Accept Language Header
    |--------------------------------------------------------------------------
    |
    | Parse the Accept-Language header from the browser.
    |
    */
    'use_accept_language_header' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | Duration (in minutes) to cache locale translations and settings.
    |
    */
    'cache_duration' => 1440, // 24 hours
];



# Multi-Region Localization System

## Overview

This Laravel application includes a comprehensive multi-region localization system that supports multiple languages and regional variants. Currently configured for **English only**, but designed to easily add more languages in the future.

## Features

- ✅ Multi-locale support with regional variants (e.g., en_US, en_GB, en_AU)
- ✅ Automatic locale detection based on user preferences, session, organization, or browser
- ✅ User and organization-level locale preferences
- ✅ Number and currency formatting per region
- ✅ Custom Blade directives for localization
- ✅ RESTful API endpoints for locale management
- ✅ Locale switching with UI component
- ✅ RTL/LTR text direction support (for future languages)
- ✅ Session-based and database-stored preferences

## Supported Locales

### English (en)
- **en_US** - United States (USD)
- **en_GB** - United Kingdom (GBP)
- **en_CA** - Canada (CAD)
- **en_AU** - Australia (AUD)
- **en_NZ** - New Zealand (NZD)

## Architecture

### Core Components

1. **LocaleService** (`app/Services/Localization/LocaleService.php`)
   - Main service for locale detection and management
   - Handles number and currency formatting
   - Manages locale preferences

2. **SetLocale Middleware** (`app/Http/Middleware/SetLocale.php`)
   - Automatically detects and sets locale for each request
   - Checks user, organization, session, and browser preferences

3. **LocaleController** (`app/Http/Controllers/LocaleController.php`)
   - Handles locale switching requests
   - Provides API endpoints for locale information

4. **Configuration** (`config/localization.php`)
   - Central configuration for all supported locales
   - Defines regional variants and settings
   - Configures detection order and caching

5. **Database Support**
   - Migration adds `locale` and `country_code` fields to users and organizations
   - Organizations can have multiple supported locales

## Installation & Setup

### 1. Run Migration

```bash
php artisan migrate
```

This adds locale fields to `users` and `organizations` tables.

### 2. Autoload Helpers

The helper functions are already registered in `composer.json`:

```bash
composer dump-autoload
```

### 3. Configuration

All localization settings are in `config/localization.php`. Key settings:

- `default_locale`: 'en'
- `default_regional`: 'en_US'
- `detection_order`: ['user', 'session', 'organization', 'browser', 'default']

## Usage

### In Controllers

```php
use App\Services\Localization\LocaleService;

class MyController extends Controller
{
    public function index(LocaleService $localeService)
    {
        $currentLocale = $localeService->getCurrentLocale();
        $regionalLocale = $localeService->getCurrentRegionalLocale();
        
        // Format numbers
        $formatted = $localeService->formatNumber(1234.56, 2);
        
        // Format currency
        $price = $localeService->formatCurrency(99.99, 'USD');
    }
}
```

### Using Helper Functions

```php
// Get current locale
$locale = current_locale(); // 'en'

// Get regional locale
$regional = current_regional_locale(); // 'en_US'

// Format numbers
$formatted = format_number(1234.56); // '1,234.56' (US format)

// Format currency
$price = format_currency(99.99); // '$99.99'
$price = format_currency(99.99, 'GBP'); // '£99.99'

// Check locale support
if (is_locale_supported('en')) {
    // Locale is supported
}

// Get all enabled locales
$locales = enabled_locales();
```

### In Blade Templates

```blade
{{-- Display current locale --}}
@locale {{-- Outputs: en --}}

{{-- Display regional locale --}}
@regional_locale {{-- Outputs: en_US --}}

{{-- Translation with fallback --}}
@trans('common.welcome', ['name' => $appName])

{{-- Format currency --}}
@currency(99.99) {{-- Outputs: $99.99 --}}

{{-- Format numbers --}}
@number(1234.56, 2) {{-- Outputs: 1,234.56 --}}

{{-- Locale selector dropdown --}}
<x-locale-selector />

{{-- RTL/LTR support --}}
@rtl
    <div dir="rtl">This content is RTL</div>
@endrtl

@ltr
    <div dir="ltr">This content is LTR</div>
@endltr
```

### Translation Files

Language files are located in `lang/en/`:

- `common.php` - Common app-wide translations
- `auth.php` - Authentication messages
- `campaign.php` - Campaign-specific translations
- `organization.php` - Organization translations
- `locale.php` - Locale/language switching messages
- `validation.php` - Validation messages
- `passwords.php` - Password reset messages
- `pagination.php` - Pagination labels

Example usage:

```php
// In PHP
echo __('common.welcome', ['name' => 'MarketPulse']);
echo __('campaign.campaign_created');

// In Blade
{{ __('common.create') }}
{{ __('campaign.campaigns') }}
```

### API Endpoints

#### Get Available Locales
```
GET /api/locales/available
```

Response:
```json
{
  "current": "en",
  "current_regional": "en_US",
  "supported": {
    "en": {
      "name": "English",
      "native_name": "English",
      "regional": {
        "en_US": {
          "name": "English (United States)",
          "currency": "USD",
          "country_code": "US"
        }
      },
      "direction": "ltr",
      "enabled": true
    }
  },
  "enabled": { ... }
}
```

#### Switch Locale
```
GET /locale/{locale}
```

Example:
```
GET /locale/en
```

#### Switch Regional Locale
```
GET /locale/regional/{regionalLocale}
```

Example:
```
GET /locale/regional/en_GB
```

### User Preferences

Set user locale preference:

```php
$user = auth()->user();
$user->setPreferredLocale('en');

// Check if user has custom locale
if ($user->hasCustomLocale()) {
    $locale = $user->getPreferredLocale();
}
```

### Organization Preferences

Manage organization locales:

```php
$organization = Organization::find($id);

// Set default locale
$organization->setDefaultLocale('en');

// Add supported locale
$organization->addSupportedLocale('en');

// Check if locale is supported
if ($organization->supportsLocale('en')) {
    // Locale is supported
}

// Get all supported locales
$locales = $organization->getSupportedLocales();
```

## Adding New Languages

To add a new language (e.g., Spanish):

### 1. Update Configuration

Edit `config/localization.php`:

```php
'supported_locales' => [
    'en' => [ ... ],
    'es' => [
        'name' => 'Spanish',
        'native_name' => 'Español',
        'script' => 'Latn',
        'regional' => [
            'es_ES' => ['name' => 'Spanish (Spain)', 'currency' => 'EUR', 'country_code' => 'ES'],
            'es_MX' => ['name' => 'Spanish (Mexico)', 'currency' => 'MXN', 'country_code' => 'MX'],
        ],
        'direction' => 'ltr',
        'enabled' => true,
    ],
],
```

### 2. Create Language Files

Create directory `lang/es/` and copy translation files from `lang/en/`, then translate:

```
lang/es/
  ├── common.php
  ├── auth.php
  ├── campaign.php
  ├── organization.php
  ├── locale.php
  ├── validation.php
  ├── passwords.php
  └── pagination.php
```

### 3. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## Locale Detection Priority

The middleware detects locale in this order (configurable in `config/localization.php`):

1. **User Preference** - Saved in user's profile
2. **Session** - Current session locale
3. **Organization** - Organization's default locale
4. **Browser** - Accept-Language header
5. **Default** - System default (en)

## Number & Currency Formatting

The system uses PHP's `NumberFormatter` class for locale-aware formatting:

```php
// Numbers
format_number(1234.56)     // en_US: "1,234.56"
format_number(1234.56)     // en_GB: "1,234.56"

// Currency
format_currency(99.99)             // en_US: "$99.99"
format_currency(99.99, 'GBP')      // en_GB: "£99.99"
format_currency(99.99, 'EUR')      // en_GB: "€99.99"
```

## Testing

Test locale functionality:

```php
// In tests
public function test_locale_detection()
{
    $user = User::factory()->create(['locale' => 'en']);
    
    $this->actingAs($user)
        ->get('/main/1')
        ->assertSessionHas('locale', 'en');
}

public function test_currency_formatting()
{
    App::setLocale('en');
    Session::put('regional_locale', 'en_US');
    
    $formatted = format_currency(99.99);
    $this->assertEquals('$99.99', $formatted);
}
```

## Performance

- **Caching**: Locale configurations are cached for 24 hours
- **Session Storage**: Current locale stored in session to avoid repeated detection
- **Lazy Loading**: Translations loaded on-demand

## Troubleshooting

### Locale not switching

1. Check if middleware is registered in `bootstrap/app.php`
2. Verify locale is enabled in `config/localization.php`
3. Clear cache: `php artisan config:clear`

### Currency formatting not working

1. Ensure PHP `intl` extension is installed
2. Check regional locale is set correctly
3. Verify currency code is valid (ISO 4217)

### Translations not showing

1. Check language file exists in `lang/{locale}/`
2. Verify translation key exists
3. Clear view cache: `php artisan view:clear`

## Security Considerations

- Locale values are validated against supported locales
- User input is sanitized before setting locale
- Session locale can't override organization restrictions
- API endpoints respect authentication requirements

## Future Enhancements

- [ ] Add more languages (Spanish, French, German, etc.)
- [ ] URL-based locale detection (/en/dashboard)
- [ ] Locale-specific date/time formatting
- [ ] Auto-translation using AI services
- [ ] Locale-specific content (database translations)
- [ ] GeoIP-based locale detection
- [ ] User timezone integration

## References

- Laravel Localization: https://laravel.com/docs/localization
- PHP Intl Extension: https://www.php.net/manual/en/book.intl.php
- ISO 639-1 Language Codes: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
- ISO 4217 Currency Codes: https://en.wikipedia.org/wiki/ISO_4217


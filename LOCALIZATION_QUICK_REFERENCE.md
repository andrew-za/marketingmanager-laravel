# Localization Quick Reference Guide

## Quick Start

### 1. In Controllers
```php
use App\Services\Localization\LocaleService;

public function index(LocaleService $localeService)
{
    $locale = $localeService->getCurrentLocale();          // 'en'
    $regional = $localeService->getCurrentRegionalLocale(); // 'en_US'
    $price = $localeService->formatCurrency(99.99);        // '$99.99'
}
```

### 2. Using Helpers
```php
current_locale()              // 'en'
current_regional_locale()     // 'en_US'
format_number(1234.56)        // '1,234.56'
format_currency(99.99)        // '$99.99'
is_locale_supported('en')     // true
```

### 3. In Blade
```blade
@locale                       {{-- en --}}
@regional_locale              {{-- en_US --}}
@currency(99.99)              {{-- $99.99 --}}
@number(1234.56, 2)           {{-- 1,234.56 --}}

<x-locale-selector />         {{-- Dropdown selector --}}
```

### 4. Translations
```blade
{{ __('common.welcome') }}
{{ __('campaign.campaigns') }}
{{ __('common.created_successfully', ['item' => 'Campaign']) }}
```

## Helper Functions

| Function | Description | Example |
|----------|-------------|---------|
| `current_locale()` | Get current locale | `'en'` |
| `current_regional_locale()` | Get regional variant | `'en_US'` |
| `set_locale($locale)` | Set locale | `set_locale('en')` |
| `format_number($num, $decimals)` | Format number | `format_number(1234.56)` |
| `format_currency($amount, $currency)` | Format currency | `format_currency(99.99, 'USD')` |
| `is_locale_supported($locale)` | Check support | `is_locale_supported('en')` |
| `supported_locales()` | All locales | `array` |
| `enabled_locales()` | Enabled only | `array` |
| `get_currency_for_locale()` | Get currency | `'USD'` |
| `get_country_code()` | Get country | `'US'` |

## Blade Directives

| Directive | Output |
|-----------|--------|
| `@locale` | `en` |
| `@regional_locale` | `en_US` |
| `@trans('key')` | Translated text |
| `@currency(99.99)` | `$99.99` |
| `@number(1234.56)` | `1,234.56` |
| `@rtl ... @endrtl` | RTL content block |
| `@ltr ... @endltr` | LTR content block |

## Model Methods

### User
```php
$user->getPreferredLocale()      // 'en'
$user->setPreferredLocale('en')
$user->hasCustomLocale()         // bool
```

### Organization
```php
$org->getDefaultLocale()         // 'en'
$org->setDefaultLocale('en')
$org->getSupportedLocales()      // ['en']
$org->addSupportedLocale('en')
$org->removeSupportedLocale('en')
$org->supportsLocale('en')       // bool
```

## Routes

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/locale/{locale}` | Switch locale |
| GET | `/locale/regional/{regional}` | Switch regional |
| GET | `/api/locales/available` | Get all locales (API) |

## Regional Variants

| Code | Name | Currency |
|------|------|----------|
| `en_US` | English (United States) | USD |
| `en_GB` | English (United Kingdom) | GBP |
| `en_CA` | English (Canada) | CAD |
| `en_AU` | English (Australia) | AUD |
| `en_NZ` | English (New Zealand) | NZD |

## Translation Files

- `lang/en/common.php` - Common translations
- `lang/en/auth.php` - Authentication
- `lang/en/campaign.php` - Campaigns
- `lang/en/organization.php` - Organizations
- `lang/en/locale.php` - Locale management
- `lang/en/validation.php` - Validation errors
- `lang/en/passwords.php` - Password resets
- `lang/en/pagination.php` - Pagination

## Configuration

File: `config/localization.php`

Key settings:
- `default_locale` - Default language
- `default_regional` - Default region
- `detection_order` - Priority order
- `cache_duration` - Cache time (minutes)

## Common Use Cases

### Switch Locale in View
```blade
<a href="{{ route('locale.switch', ['locale' => 'en']) }}">
    English
</a>
```

### Format Product Price
```php
<span class="price">@currency($product->price)</span>
```

### Display Localized Date
```php
{{ now()->locale(current_locale())->translatedFormat('F j, Y') }}
```

### Check User's Locale
```php
@if(current_locale() === 'en')
    {{-- English-specific content --}}
@endif
```

### API Response with Formatted Values
```php
return response()->json([
    'price' => format_currency($amount),
    'quantity' => format_number($qty),
    'locale' => current_locale(),
]);
```

## Testing

```php
// Set locale in tests
App::setLocale('en');
Session::put('regional_locale', 'en_US');

// Test locale switching
$this->get('/locale/en')
    ->assertSessionHas('locale', 'en');

// Test formatting
$formatted = format_currency(99.99);
$this->assertStringContainsString('99.99', $formatted);
```

## Troubleshooting

**Locale not changing?**
1. Clear cache: `php artisan config:clear`
2. Check middleware is registered
3. Verify locale is enabled in config

**Formatting issues?**
1. Check PHP `intl` extension
2. Verify regional locale is set
3. Check currency code is valid

**Translations missing?**
1. Check file exists: `lang/en/file.php`
2. Clear views: `php artisan view:clear`
3. Check translation key

## Environment Variables

Add to `.env`:
```env
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
```

## Performance Tips

- Translations are cached automatically
- Use `remember()` for heavy operations
- Cache duration: 24 hours (configurable)
- Session stores current locale



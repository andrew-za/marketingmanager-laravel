<?php

namespace App\Providers;

use App\Services\Localization\LocaleService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for localization features
 */
class LocalizationServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->app->singleton(LocaleService::class, function ($app) {
            return new LocaleService($app->make('request'));
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $this->registerBladeDirectives();
    }

    /**
     * Register custom Blade directives for localization
     */
    protected function registerBladeDirectives(): void
    {
        // @locale directive - outputs current locale
        Blade::directive('locale', function () {
            return "<?php echo current_locale(); ?>";
        });

        // @regional_locale directive - outputs current regional locale
        Blade::directive('regional_locale', function () {
            return "<?php echo current_regional_locale(); ?>";
        });

        // @trans directive - enhanced translation with fallback
        Blade::directive('trans', function ($expression) {
            return "<?php echo __({$expression}); ?>";
        });

        // @currency directive - format currency
        Blade::directive('currency', function ($expression) {
            return "<?php echo format_currency({$expression}); ?>";
        });

        // @number directive - format number
        Blade::directive('number', function ($expression) {
            return "<?php echo format_number({$expression}); ?>";
        });

        // @locale_selector directive - display locale selector dropdown
        Blade::directive('locale_selector', function ($expression) {
            return "<?php echo view('components.locale-selector', {$expression})->render(); ?>";
        });

        // @rtl directive - check if current locale is RTL
        Blade::directive('rtl', function () {
            return "<?php if(locale_service()->getLocaleDetails(current_locale())['direction'] ?? 'ltr' === 'rtl'): ?>";
        });

        // @endrtl directive
        Blade::directive('endrtl', function () {
            return "<?php endif; ?>";
        });

        // @ltr directive - check if current locale is LTR
        Blade::directive('ltr', function () {
            return "<?php if(locale_service()->getLocaleDetails(current_locale())['direction'] ?? 'ltr' === 'ltr'): ?>";
        });

        // @endltr directive
        Blade::directive('endltr', function () {
            return "<?php endif; ?>";
        });
    }
}


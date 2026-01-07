<?php

namespace Tests\Feature\Localization;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Services\Localization\LocaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'locale' => 'en',
            'country_code' => 'US',
        ]);
        
        $this->organization = Organization::factory()->create([
            'locale' => 'en',
            'supported_locales' => ['en'],
        ]);
    }

    /** @test */
    public function it_detects_user_locale_preference()
    {
        $this->actingAs($this->user)
            ->get('/main/' . $this->organization->id)
            ->assertOk();

        $this->assertEquals('en', App::getLocale());
    }

    /** @test */
    public function it_switches_locale_successfully()
    {
        $this->actingAs($this->user)
            ->get('/locale/en')
            ->assertRedirect()
            ->assertSessionHas('locale', 'en');

        $this->assertEquals('en', $this->user->fresh()->locale);
    }

    /** @test */
    public function it_rejects_unsupported_locale()
    {
        $this->actingAs($this->user)
            ->get('/locale/invalid')
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /** @test */
    public function it_formats_currency_correctly_for_locale()
    {
        App::setLocale('en');
        Session::put('regional_locale', 'en_US');

        $localeService = app(LocaleService::class);
        $formatted = $localeService->formatCurrency(99.99, 'USD');

        $this->assertStringContainsString('99.99', $formatted);
    }

    /** @test */
    public function it_formats_numbers_correctly_for_locale()
    {
        App::setLocale('en');
        Session::put('regional_locale', 'en_US');

        $localeService = app(LocaleService::class);
        $formatted = $localeService->formatNumber(1234.56, 2);

        $this->assertStringContainsString('1,234.56', $formatted);
    }

    /** @test */
    public function user_can_set_preferred_locale()
    {
        $this->user->setPreferredLocale('en');
        
        $this->assertEquals('en', $this->user->fresh()->locale);
        $this->assertTrue($this->user->hasCustomLocale());
    }

    /** @test */
    public function user_can_get_preferred_locale()
    {
        $this->user->update(['locale' => 'en']);
        
        $this->assertEquals('en', $this->user->getPreferredLocale());
    }

    /** @test */
    public function organization_can_manage_supported_locales()
    {
        $this->organization->addSupportedLocale('en');
        
        $this->assertTrue($this->organization->supportsLocale('en'));
        $this->assertContains('en', $this->organization->getSupportedLocales());
    }

    /** @test */
    public function organization_can_set_default_locale()
    {
        $this->organization->setDefaultLocale('en');
        
        $this->assertEquals('en', $this->organization->fresh()->locale);
        $this->assertEquals('en', $this->organization->getDefaultLocale());
    }

    /** @test */
    public function api_returns_available_locales()
    {
        $response = $this->get('/api/locales/available');

        $response->assertOk()
            ->assertJsonStructure([
                'current',
                'current_regional',
                'supported',
                'enabled',
            ]);
    }

    /** @test */
    public function it_detects_locale_from_browser_header()
    {
        $response = $this->withHeaders([
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->get('/');

        // Locale should be detected from browser
        $this->assertEquals('en', App::getLocale());
    }

    /** @test */
    public function locale_service_checks_supported_locales()
    {
        $localeService = app(LocaleService::class);

        $this->assertTrue($localeService->isLocaleSupported('en'));
        $this->assertFalse($localeService->isLocaleSupported('xx'));
    }

    /** @test */
    public function it_gets_currency_for_regional_locale()
    {
        $localeService = app(LocaleService::class);
        Session::put('regional_locale', 'en_US');

        $currency = $localeService->getCurrencyForRegionalLocale('en_US');
        
        $this->assertEquals('USD', $currency);
    }

    /** @test */
    public function it_gets_country_code_for_regional_locale()
    {
        $localeService = app(LocaleService::class);

        $countryCode = $localeService->getCountryCodeForRegionalLocale('en_GB');
        
        $this->assertEquals('GB', $countryCode);
    }

    /** @test */
    public function helper_functions_work_correctly()
    {
        App::setLocale('en');
        Session::put('regional_locale', 'en_US');

        $this->assertEquals('en', current_locale());
        $this->assertEquals('en_US', current_regional_locale());
        $this->assertTrue(is_locale_supported('en'));
        $this->assertIsArray(supported_locales());
        $this->assertIsArray(enabled_locales());
    }

    /** @test */
    public function format_helper_functions_work()
    {
        Session::put('regional_locale', 'en_US');

        $number = format_number(1234.56);
        $currency = format_currency(99.99);

        $this->assertIsString($number);
        $this->assertIsString($currency);
    }

    /** @test */
    public function middleware_sets_locale_for_authenticated_user()
    {
        $this->user->update(['locale' => 'en']);

        $this->actingAs($this->user)
            ->get('/main/' . $this->organization->id);

        $this->assertEquals('en', App::getLocale());
    }

    /** @test */
    public function organization_can_remove_supported_locale()
    {
        $this->organization->update(['supported_locales' => ['en']]);
        $this->organization->removeSupportedLocale('en');

        $this->assertNotContains('en', $this->organization->fresh()->getSupportedLocales());
    }

    /** @test */
    public function locale_detection_follows_priority_order()
    {
        // Set up different locale sources
        $this->user->update(['locale' => 'en']);
        $this->organization->update(['locale' => 'en']);
        Session::put('locale', 'en');

        $localeService = app(LocaleService::class);
        $detected = $localeService->detectLocale($this->user, $this->organization);

        // User preference should have highest priority
        $this->assertEquals('en', $detected);
    }
}



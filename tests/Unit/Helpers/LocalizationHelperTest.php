<?php

namespace Tests\Unit\Helpers;

use App\Services\Localization\LocaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\TestCase;

class LocalizationHelperTest extends TestCase
{
    use RefreshDatabase;

    public function testCurrentLocale(): void
    {
        App::setLocale('en');
        $this->assertEquals('en', current_locale());

        App::setLocale('es');
        $this->assertEquals('es', current_locale());
    }

    public function testSetLocale(): void
    {
        $localeServiceMock = Mockery::mock(LocaleService::class);
        $localeServiceMock->shouldReceive('setLocale')
            ->once()
            ->with('fr');

        App::instance(LocaleService::class, $localeServiceMock);
        
        set_locale('fr');
    }

    public function testSupportedLocales(): void
    {
        $localeServiceMock = Mockery::mock(LocaleService::class);
        $localeServiceMock->shouldReceive('getSupportedLocales')
            ->once()
            ->andReturn(['en', 'es', 'fr']);

        App::instance(LocaleService::class, $localeServiceMock);
        
        $locales = supported_locales();
        $this->assertEquals(['en', 'es', 'fr'], $locales);
    }

    public function testIsLocaleSupported(): void
    {
        $localeServiceMock = Mockery::mock(LocaleService::class);
        $localeServiceMock->shouldReceive('isLocaleSupported')
            ->with('en')
            ->once()
            ->andReturn(true);
        $localeServiceMock->shouldReceive('isLocaleSupported')
            ->with('xx')
            ->once()
            ->andReturn(false);

        App::instance(LocaleService::class, $localeServiceMock);
        
        $this->assertTrue(is_locale_supported('en'));
        $this->assertFalse(is_locale_supported('xx'));
    }

    public function testFormatNumber(): void
    {
        $localeServiceMock = Mockery::mock(LocaleService::class);
        $localeServiceMock->shouldReceive('formatNumber')
            ->with(1234.56, 2)
            ->once()
            ->andReturn('1,234.56');

        App::instance(LocaleService::class, $localeServiceMock);
        
        $result = format_number(1234.56, 2);
        $this->assertEquals('1,234.56', $result);
    }

    public function testFormatCurrency(): void
    {
        $localeServiceMock = Mockery::mock(LocaleService::class);
        $localeServiceMock->shouldReceive('formatCurrency')
            ->with(100.50, 'USD')
            ->once()
            ->andReturn('$100.50');

        App::instance(LocaleService::class, $localeServiceMock);
        
        $result = format_currency(100.50, 'USD');
        $this->assertEquals('$100.50', $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}


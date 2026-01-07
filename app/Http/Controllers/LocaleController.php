<?php

namespace App\Http\Controllers;

use App\Services\Localization\LocaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller for handling locale switching
 */
class LocaleController extends Controller
{
    protected LocaleService $localeService;

    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }

    /**
     * Switch the application locale
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (!$this->localeService->isLocaleSupported($locale)) {
            return back()->with('error', __('common.error') . ': ' . __('locale.unsupported_locale'));
        }

        $this->localeService->setLocale($locale);

        // If user is authenticated, save their preference
        if ($user = $request->user()) {
            $user->setPreferredLocale($locale);
        }

        return back()->with('success', __('locale.locale_changed_successfully'));
    }

    /**
     * Switch the regional locale
     */
    public function switchRegional(Request $request, string $regionalLocale): RedirectResponse
    {
        $locale = substr($regionalLocale, 0, 2);

        if (!$this->localeService->isLocaleSupported($locale)) {
            return back()->with('error', __('common.error') . ': ' . __('locale.unsupported_locale'));
        }

        $this->localeService->setRegionalLocale($regionalLocale);

        return back()->with('success', __('locale.regional_locale_changed_successfully'));
    }

    /**
     * Get available locales (API endpoint)
     */
    public function available(): array
    {
        return [
            'current' => $this->localeService->getCurrentLocale(),
            'current_regional' => $this->localeService->getCurrentRegionalLocale(),
            'supported' => $this->localeService->getSupportedLocales(),
            'enabled' => $this->localeService->getEnabledLocales(),
        ];
    }
}



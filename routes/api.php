<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Campaign\CampaignController;
use App\Http\Controllers\LocaleController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Locale API endpoints (public)
Route::get('locales/available', [LocaleController::class, 'available'])->name('api.locales.available');

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/publish', [CampaignController::class, 'publish'])->name('campaigns.publish');
});


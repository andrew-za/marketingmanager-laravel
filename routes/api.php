<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Campaign\CampaignController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/publish', [CampaignController::class, 'publish'])->name('campaigns.publish');
});


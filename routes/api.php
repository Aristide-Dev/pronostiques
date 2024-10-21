<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScraperController;
use App\Http\Controllers\ForebetScraperController;
use App\Http\Controllers\MatchAggregatorController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/scrape-forebet', [ScraperController::class, 'scrapeForebet']);
Route::get('/analyze-matches', [MatchAggregatorController::class, 'getCommonMatchesJson']);
// Route::get('/scrape-forebet', [ForebetScraperController::class, 'scrape']);

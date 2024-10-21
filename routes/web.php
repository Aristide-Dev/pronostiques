<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MatchAggregatorController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::get('/analyze-matches', [MatchAggregatorController::class, 'showCommonMatches']);

require __DIR__.'/auth.php';

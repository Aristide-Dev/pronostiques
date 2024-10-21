<?php

namespace App\Http\Controllers;

use App\Services\MatchAggregatorService;
use Illuminate\Http\Request;

class MatchAggregatorController extends Controller
{
    protected $aggregatorService;

    public function __construct(MatchAggregatorService $aggregatorService)
    {
        $this->aggregatorService = $aggregatorService;
    }

    public function showCommonMatches()
    {
        $analyzedMatches = $this->aggregatorService->aggregateMatches();
        return view('matches', ['analyzedMatches' => $analyzedMatches]);
    }

    public function getCommonMatchesJson()
    {
        $analyzedMatches = $this->aggregatorService->aggregateMatches();
        return response()->json($analyzedMatches);
    }
}

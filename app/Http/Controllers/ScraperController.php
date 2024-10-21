<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ForebetScraperService;

class ScraperController extends Controller
{
    public function scrapeForebet(ForebetScraperService $scraper)
    {
        $matches = $scraper->scrape();
        return response()->json($matches);
    }
}

<?php

namespace App\Services\Scraper;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\BrowserKit\HttpBrowser;

class ScraperService
{
    protected $client;

    public function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create([
            'timeout' => 60,
        ]));
    }

    protected function formatTime($time)
    {
        return str_replace(':', 'h:', $time);
    }
}
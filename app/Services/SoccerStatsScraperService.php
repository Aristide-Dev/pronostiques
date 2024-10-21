<?php

namespace App\Services;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class SoccerStatsScraperService
{
    protected $client;

    public function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }

    public function scrape()
    {
        $url = 'https://www.soccerstats.com/latest.asp?league=england';
        $crawler = $this->client->request('GET', $url);

        $matches = $crawler->filter('.odd')->each(function (Crawler $node) {
            $teams = $node->filter('.trow.text-center')->text('');
            $stats = $node->filter('.odd.text-center')->text('');

            return [
                'teams' => strtolower(trim($teams)),
                'stats' => $stats,
            ];
        });

        return $matches;
    }
}

<?php

namespace App\Services;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class WinDrawWinScraperService
{
    protected $client;

    public function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }

    public function scrape()
    {
        $url = 'https://www.windrawwin.com/predictions/';
        $crawler = $this->client->request('GET', $url);

        $matches = $crawler->filter('.match-row')->each(function (Crawler $node) {
            $teams = $node->filter('.match-tn')->text('');
            $prediction = $node->filter('.match-tp')->text('');

            return [
                'teams' => strtolower(trim($teams)),
                'prediction' => $prediction,
            ];
        });

        return $matches;
    }
}

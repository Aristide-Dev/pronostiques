<?php

namespace App\Services;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class BettingProScraperService
{
    protected $client;

    public function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }

    public function scrape()
    {
        $url = 'https://www.bettingpro.com/football-predictions/';
        $crawler = $this->client->request('GET', $url);

        $matches = $crawler->filter('.pro-prediction')->each(function (Crawler $node) {
            $teams = $node->filter('.teams')->text('');
            $prediction = $node->filter('.outcome')->text('');
            $confidence = $node->filter('.confidence')->text('');

            return [
                'teams' => strtolower(trim($teams)),
                'prediction' => $prediction,
                'confidence' => rtrim($confidence, '%'),
            ];
        });

        return $matches;
    }
}

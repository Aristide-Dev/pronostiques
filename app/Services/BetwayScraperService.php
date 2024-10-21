<?php

namespace App\Services;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class BetwayScraperService
{
    protected $client;

    public function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }

    public function scrape()
    {
        $url = 'https://www.betway.com/en/sports/football/predictions';
        $crawler = $this->client->request('GET', $url);

        $matches = $crawler->filter('.betway-match')->each(function (Crawler $node) {
            $teams = $node->filter('.teams')->text('');
            $prediction = $node->filter('.prediction')->text('');
            $odds = $node->filter('.odds')->text('');

            return [
                'teams' => strtolower(trim($teams)),
                'prediction' => $prediction,
                'odds' => $odds,
            ];
        });

        return $matches;
    }
}

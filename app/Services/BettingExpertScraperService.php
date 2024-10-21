<?php

namespace App\Services;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class BettingExpertScraperService
{
    protected $client;

    public function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }

    public function scrape()
    {
        $url = 'https://www.bettingexpert.com/football';
        $crawler = $this->client->request('GET', $url);

        $matches = $crawler->filter('.match-container')->each(function (Crawler $node) {
            $homeTeam = $node->filter('.home-team')->text('');
            $awayTeam = $node->filter('.away-team')->text('');
            $prediction = $node->filter('.prediction')->text('');

            return [
                'teams' => strtolower(trim("$homeTeam vs $awayTeam")),
                'prediction' => $prediction,
            ];
        });

        return $matches;
    }
}

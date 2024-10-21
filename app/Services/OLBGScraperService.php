<?php

namespace App\Services;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class OLBGScraperService
{
    protected $client;

    public function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }

    public function scrape()
    {
        $url = 'https://www.olbg.com/football-predictions';
        $crawler = $this->client->request('GET', $url);

        $matches = $crawler->filter('.olbg-match')->each(function (Crawler $node) {
            $teams = $node->filter('.teams')->text('');
            $prediction = $node->filter('.prediction')->text('');

            return [
                'teams' => strtolower(trim($teams)),
                'prediction' => $prediction,
            ];
        });

        return $matches;
    }
}

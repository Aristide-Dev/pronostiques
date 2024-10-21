<?php

namespace App\Services;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class ForebetScraperService
{
    protected $client;

    public function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }

    public function scrape()
    {
        $url = 'https://www.forebet.com/fr/pronostics-pour-aujourd-hui';
        $crawler = $this->client->request('GET', $url);
        dd($crawler);


        $matches = $crawler->filter('.rcnt.tr_0')->each(function (Crawler $node) {
            $homeTeam = $node->filter('.homeTeam')->text('');
            $awayTeam = $node->filter('.awayTeam')->text('');
            $prediction = $node->filter('.forebet')->text('');
            $probability = $node->filter('.prob')->text('');

            return [
                'teams' => strtolower(trim("$homeTeam vs $awayTeam")),
                'prediction' => $prediction,
                'probability' => rtrim($probability, '%'),
            ];
        });

        return $matches;
    }
}

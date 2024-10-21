<?php

namespace App\Services;

use App\Services\Scraper\ScraperService;
use Symfony\Component\DomCrawler\Crawler;

class BetExplorerScraperService extends ScraperService
{

    public function scrape()
    {
        $url = 'https://www.betexplorer.com/football'; // Remplace par l'URL exacte des pronostics
        $crawler = $this->client->request('GET', $url);

        // Vérifier si la page a bien été chargée
        if ($crawler->filter('.table-main')->count() === 0) {
            throw new \Exception('La table des pronostics n\'a pas été trouvée sur la page.');
        }

        // Extraire les lignes de la table
        $rows = $crawler->filter('.table-main tr');
        $matches = [];

        $currentTournament = '';

        $rows->each(function (Crawler $row) use (&$matches, &$currentTournament) {
            // Vérifier si c'est une ligne de tournoi
            if ($row->filter('.js-tournament')->count() > 0) {
        
                $currentTournament = $row->filter('.table-main__tournament')->text('', false);

                // dd($currentTournament);
                return;
            }else{
                

                // dd(['vide de vider']);

            }

            // Vérifier si c'est une ligne de match
            if ($row->attr('data-dt')) {
                // Extraire l'heure et les équipes
                $teamInfo = $row->filter('td.h-text-left a')->text('', false);
                $time = $row->filter('td.h-text-left .table-main__time')->text('', false);
                $teams = $row->filter('td.h-text-left a')->text('', false);

                // Nettoyer les données
                $teams = trim($teams);
                $teams = strtolower($teams); // Pour une normalisation facile

                // Extraire les cotes
                $odds = $row->filter('td.table-main__odds button')->each(function (Crawler $button) {
                    return $button->attr('data-odd');
                });

                // Vérifier qu'il y a bien 3 cotes
                if (count($odds) === 3) {
                    $matches[] = [
                        'pays' => $this->getPays($currentTournament),
                        'competition' => $this->getCompetition($currentTournament),
                        'tournament' => trim($currentTournament),
                        'time' => trim($time),
                        'teams' => $teams,
                        'odds' => [
                            '1' => $odds[0],
                            'X' => $odds[1],
                            '2' => $odds[2],
                        ],
                    ];
                }
            }
        });

        dd($matches);

        return $matches;
    }

    private function getPays($currentTournament)
    {
        $tournament = explode(':', $currentTournament);
        return trim($tournament[0]);
    }

    private function getCompetition($currentTournament)
    {
        $tournament = explode(':', $currentTournament);
        return trim($tournament[1]);
    }
}

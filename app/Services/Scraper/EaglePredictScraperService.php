<?php

namespace App\Services\Scraper;

use App\Services\Scraper\ScraperService;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class EaglePredictScraperService extends ScraperService
{
    public $sourceName ="AccuratePredictScraper";

    
    public function scrape()
    {
        $url = 'https://eaglepredict.com/';
        Log::info("Navigating to URL: $url");
        $crawler = $this->client->request('GET', $url);

        // Vérifier si la page a bien été chargée
        if ($crawler->filter('.predictions-table')->count() === 0) {
            Log::error('La table des prédictions n\'a pas été trouvée sur la page.');
            throw new \Exception('La table des prédictions n\'a pas été trouvée sur la page.');
        }

        // Extraire les lignes de la table
        $rows = $crawler->filter('.predictions-table tbody tr');
        $matches = [];

        $currentLeague = '';

        // dd($rows);

        $rows->each(function (Crawler $row) use (&$matches, &$currentLeague) {
            // Vérifier si c'est une ligne d'en-tête de ligue
            if ($row->attr('class') === 'predictions-league-header') {
                $currentLeague = $row->attr('data-league') ?: '';
                Log::info("Current League: $currentLeague");
                return;
            }

            // Vérifier si c'est une ligne de match
            if ($row->attr('data-league')) {
                try {
                    // Extraire la date et l'heure
                    $dateDiv = $row->filter('.prediction-table-date div')->first()->text('', false);
                    $timeDiv = $row->filter('.prediction-table-date div')->eq(1)->text('', false);
                    $date = trim($dateDiv);
                    $time = trim($timeDiv);
                    Log::info("Match Date: $date, Time: $time");

                    // Extraire les équipes
                    $teamsText = $row->filter('.prediction-table-teams')->text('', false);
                    $teams = strtolower(trim($teamsText));
                    
                    $homeTeam = null;
                    $awayTeam = null;
                    // Split teams on ' vs '
                    $teamsArray = explode(' vs ', $teamsText);
                    if (count($teamsArray) === 2) {
                        $homeTeam = strtolower(trim($teamsArray[0]));
                        $awayTeam = strtolower(trim($teamsArray[1]));
                        $teams = "$homeTeam - $awayTeam";
                    } else {
                        Log::warning("Format d'équipes inattendu pour le match: $teamsText");
                        $teams = strtolower(trim($teamsText)); // Fallback
                    }
                    Log::info("Match Teams: $teams");

                    // Extraire les cotes
                    $oddsDiv = $row->filter('.prediction-table-odd div')->first()->text('', false);
                    $odds = $this->sanitizeString($oddsDiv);
                    Log::info("Match Odds: $odds");

                    // Extraire la prédiction
                    $prediction = $row->filter('.prediction-table-prediction')->text('', false);
                    $prediction = $this->sanitizeString(trim($prediction));
                    Log::info("Match Prediction: $prediction");

                    // Extraire les cotes de manière détaillée si nécessaire
                    // Par exemple, si les cotes sont des valeurs uniques (comme dans ton HTML)
                    // Sinon, ajuster selon la structure réelle

                    // Ajouter le match aux résultats
                    if (!empty($currentLeague) && !empty($teams) && !empty($odds)) {
                        $matches[] = [
                            'source' =>$this->sourceName,
                            'pays' => $this->getPays($currentLeague),
                            'competition' => $this->getCompetition($currentLeague),
                            'tournament' => trim($currentLeague),
                            'homeTeam' => $homeTeam,
                            'awayTeam' => $awayTeam,
                            'date' => $date,
                            'time' => $time,
                            'teams' => $teams,
                            'odds' => $odds,
                            'prediction' => $prediction,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur lors de l\'extraction des données d\'un match:', ['message' => $e->getMessage()]);
                }
            }
        });

        Log::info("Nombre de matchs scrappés: " . count($matches));
        return $matches;
    }

    public function aggregateMatches(array $matchesByKey): array
    {
        $eaglePredictMatches = $this->scrape();
        // Processus des matchs de EaglePredict
        foreach ($eaglePredictMatches as $match) {
            $date = $match['date'];
            $time = $match['time'];
            $teams = strtolower(trim($match['teams']));
            $competition = $match['competition'] ?? 'Inconnu';

            // Créer une clé unique pour chaque match
            $key = $date . '|' . $teams . '|' . $time;

            // Formater le nom du match
            $formattedMatchName = ucwords(str_replace(' - ', ' vs ', $teams));

            // Formater l'heure du match
            $formattedTime = $this->formatTime($time);

            if (!isset($matchesByKey[$key])) {
                $matchesByKey[$key] = [
                    'match' => $formattedMatchName,
                    'heure du match' => $formattedTime,
                    'competition' => $competition,
                    'predictions' => []
                ];
            }

            // Ajouter les prédictions de EaglePredict
            $matchesByKey[$key]['predictions']['EaglePredict'] = [
                'cote' => $match['odds'] ?? 'N/A',
                'parie' => $match['prediction'] ?? 'N/A'
            ];
        }
        return $matchesByKey;
    }

    private function getPays($currentLeague)
    {
        $parts = explode(' - ', $currentLeague);
        return isset($parts[1]) ? trim($parts[1]) : 'Inconnu';
    }

    private function getCompetition($currentLeague)
    {
        $parts = explode(' - ', $currentLeague);
        return isset($parts[0]) ? trim($parts[0]) : 'Inconnu';
    }

    /**
     * Nettoie et assure que la chaîne est encodée en UTF-8.
     *
     * @param string $string
     * @return string
     */
    private function sanitizeString($string)
    {
        // Remplacer les caractères non UTF-8 par un espace ou un point d'interrogation
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $string = preg_replace('/[^\x20-\x7E]/u', '', $string); // Supprime les caractères non imprimables
        return $string;
    }
}

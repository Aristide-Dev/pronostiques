<?php

namespace App\Services\Scraper;

use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class AccuratePredictScraperService extends ScraperService
{
    public $sourceName = "AccuratePredict";

    /**
     * Scrape les prédictions de AccuratePredict.
     *
     * @return array
     * @throws \Exception
     */
    public function scrape()
    {
        $url = 'https://accuratepredict.com/today-football-predictions';
        Log::info("Navigating to URL: $url");

        // Envoyer une requête GET à l'URL cible
        $crawler = $this->client->request('GET', $url);

        // Vérifier si la page a bien été chargée en recherchant la table des prédictions
        if ($crawler->filter('.prediction-table')->count() === 0) {
            Log::error('La table des prédictions n\'a pas été trouvée sur la page.');
            throw new \Exception('La table des prédictions n\'a pas été trouvée sur la page.');
        }

        // Extraire toutes les tables de prédictions
        $tables = $crawler->filter('.prediction-table');
        $matches = [];

        foreach ($tables as $table) {
            $tableCrawler = new Crawler($table);

            // Extraire le nom de la ligue depuis l'en-tête de la table
            $leagueName = $this->sanitizeString($tableCrawler->filter('thead.league_title th')->first()->text('', false));
            $leagueName = trim($leagueName);
            Log::info("Scraping Ligue: $leagueName");

            // Extraire les lignes de matchs depuis le tbody
            $rows = $tableCrawler->filter('tbody tr.match-row');

            $rows->each(function (Crawler $row) use ($leagueName, &$matches) {
                try {
                    // Extraire les données du match
                    $date = $this->sanitizeString(trim($row->filter('.match-info-td .timer .time_cont .date')->text('', false)));
                    $time = $this->sanitizeString(trim($row->filter('.match-info-td .timer .time_cont .day')->text('', false)));

                    // Extraire les noms des équipes
                    $homeTeam = $this->sanitizeString(trim($row->filter('.match-info-td .club-info .club')->eq(0)->filter('.club-name')->text('', false)));
                    $awayTeam = $this->sanitizeString(trim($row->filter('.match-info-td .club-info .club')->eq(1)->filter('.club-name')->text('', false)));
                    $teams = strtolower("$homeTeam - $awayTeam");
                    Log::info("Match: $teams");

                    // Extraire les probabilités
                    $probabilities = [];
                    $probabilitySpans = $row->filter('.posibility span');
                    if ($probabilitySpans->count() === 3) {
                        $probabilities['homeTeam WIN'] = $this->sanitizeString(trim($probabilitySpans->eq(0)->text('', false))) . '%';
                        $probabilities['Match NUL'] = $this->sanitizeString(trim($probabilitySpans->eq(1)->text('', false))) . '%';
                        $probabilities['awayTeam WIN'] = $this->sanitizeString(trim($probabilitySpans->eq(2)->text('', false))) . '%';
                        Log::info("Probabilities: " . json_encode($probabilities));
                    } else {
                        Log::warning("Nombre de probabilités inattendu pour le match: $teams");
                    }

                    // Extraire le conseil (Tip)
                    $tip = $this->sanitizeString(trim($row->filter('.tips a')->text('', false)));
                    Log::info("Tip: $tip");

                    // Extraire la cote (Odds)
                    $odds = $this->sanitizeString(trim($row->filter('.odd a')->text('', false)));
                    Log::info("Odds: $odds");

                    // Ajouter les données du match à l'array $matches
                    $matches[] = [
                        'source' => $this->sourceName,
                        'pays' => $this->getPays($leagueName),
                        'competition' => $this->getCompetition($leagueName),
                        'tournament' => $leagueName,
                        'homeTeam' => $homeTeam,
                        'awayTeam' => $awayTeam,
                        'date' => $date,
                        'time' => $time,
                        'teams' => $teams,
                        'probabilities' => $probabilities,
                        'parie' => $tip, // Changement de 'tip' à 'parie'
                        'odds' => $odds,
                    ];
                } catch (\Exception $e) {
                    Log::error('Erreur lors de l\'extraction des données d\'un match:', ['message' => $e->getMessage()]);
                }
            });
        }

        Log::info("Nombre total de matchs scrappés: " . count($matches));

        return $matches;
    }

    public function aggregateMatches(array $matchesByKey): array
    {
        $accuratePredictMatches = $this->scrape();
        // Processus des matchs de EaglePredict
        foreach ($accuratePredictMatches as $match) {
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

            // Mapper les probabilités
            $probabilities = [];
            if (isset($match['probabilities'])) {
                $probabilities = [
                    "homeTeam WIN" => $match['probabilities']['homeTeam WIN'] ?? 'N/A',
                    "Match NUL" => $match['probabilities']['Match NUL'] ?? 'N/A',
                    "awayTeam WIN" => $match['probabilities']['awayTeam WIN'] ?? 'N/A',
                ];
            }

            // Ajouter les prédictions d'AccuratePredict
            $matchesByKey[$key]['predictions']['AccuratePredict'] = [
                'cote' => $match['odds'] ?? 'N/A',
                'probabilities' => $probabilities,
                'parie' => $match['parie'] ?? 'N/A'
            ];
        }
        return $matchesByKey;
    }

    /**
     * Extraire le pays à partir du nom de la ligue.
     *
     * @param string $leagueName
     * @return string
     */
    private function getPays($leagueName)
    {
        // Adapter selon le format exact des noms de ligues
        // Par exemple, si le nom de la ligue est "England Premier League", alors le pays est "England"
        $parts = explode(' ', $leagueName);
        return isset($parts[0]) ? trim($parts[0]) : 'Inconnu';
    }

    /**
     * Extraire la compétition à partir du nom de la ligue.
     *
     * @param string $leagueName
     * @return string
     */
    private function getCompetition($leagueName)
    {
        // Supposons que la compétition est le reste du nom de la ligue après le pays
        $parts = explode(' ', $leagueName, 2);
        return isset($parts[1]) ? trim($parts[1]) : 'Inconnu';
    }

    /**
     * Nettoie et assure que la chaîne est encodée en UTF-8.
     *
     * @param string $string
     * @return string
     */
    private function sanitizeString($string)
    {
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $string = preg_replace('/[^\x20-\x7E]/u', '', $string);
        return $string;
    }
}

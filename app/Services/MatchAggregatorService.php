<?php

namespace App\Services;

use App\Services\TeamFormService;
use Illuminate\Support\Facades\Log;
use App\Services\Scraper\EaglePredictScraperService;
use App\Services\Scraper\AccuratePredictScraperService;

class MatchAggregatorService
{
    protected $eaglePredict;
    protected $accuratePredict;

    public function __construct(
        EaglePredictScraperService $eaglePredict,
        AccuratePredictScraperService $accuratePredict
    ) 
    {
        $this->eaglePredict = $eaglePredict;
        $this->accuratePredict = $accuratePredict;
    }

    /**
     * Agrège les matchs des différentes sources et structure les données.
     *
     * @return array
     */
    public function aggregateMatches()
    {
        try {
            // Scraper les données de chaque site
            // $eaglePredictMatches = $this->eaglePredict->scrape();
            // $accuratePredictMatches = $this->accuratePredict->scrape();

            // Log::info("Nombre de matchs EaglePredict: " . count($eaglePredictMatches));
            // Log::info("Nombre de matchs AccuratePredict: " . count($accuratePredictMatches));

            // Initialiser un tableau pour stocker tous les matchs par clé unique
            $matchesByKey = [];
            $matchesByKey = $this->eaglePredict->aggregateMatches($matchesByKey);
            $matchesByKey = $this->accuratePredict->aggregateMatches($matchesByKey);
            // dd($matchesByKey);
            
            // Historiques des rencontres
            $teamFormService = new TeamFormService();

            // Grouper les matchs par date
            $groupedByDate = [];

            foreach ($matchesByKey as $key => $match) {
                $date = $match['heure du match']; // Erreur ici: devrait être $match['date']
                // Correction:
                $date = explode('|', $key)[0];
                unset($match['date']); // Supprimer la clé 'date' du match individuel

                $teams = explode(' Vs ', $match['match']);
                $homeTeam = $teams[0];
                $awayTeam = $teams[1];
                $match['recent_form'] = [
                    'team_a' => null,
                    'team_b' => null,
                    // 'team_a' => $teamFormService->getRecentForm($homeTeam) ?? null,
                    // 'team_b' => $teamFormService->getRecentForm($awayTeam) ?? null,
                ];

                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [];
                }

                $groupedByDate[$date][] = [
                    'match' => $match['match'],
                    'heure du match' => $match['heure du match'],
                    'competition' => $match['competition'],
                    'predictions' => $match['predictions'],
                    'recent_form' => $match['recent_form'],
                ];
            }

            Log::info("Nombre total de dates scrappées: " . count($groupedByDate));

            return $groupedByDate;

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'agrégation des matchs:', ['message' => $e->getMessage()]);
            return ['error' => 'Erreur lors de l\'agrégation des matchs: ' . $e->getMessage()];
        }
    }

    /**
     * Formate l'heure du match de "HH:MM" à "HHh:MM".
     *
     * @param string $time
     * @return string
     */
    private function formatTime($time)
    {
        return str_replace(':', 'h:', $time);
    }
}

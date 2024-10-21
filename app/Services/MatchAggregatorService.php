<?php

namespace App\Services;

use App\Services\ForebetScraperService;
use App\Services\BetExplorerScraperService;
use App\Services\SoccerStatsScraperService;
use App\Services\PredictZScraperService;
use App\Services\WinDrawWinScraperService;
use App\Services\BettingExpertScraperService;
use App\Services\SmartBetsScraperService;
use App\Services\BettingProScraperService;
use App\Services\OLBGScraperService;
use App\Services\TipstrrScraperService;
use App\Services\BetwayScraperService;
use App\Services\PredictZProScraperService;

class MatchAggregatorService
{
    protected $forebet;
    protected $betExplorer;
    protected $soccerStats;
    protected $predictZ;
    protected $winDrawWin;
    protected $bettingExpert;
    protected $smartBets;
    protected $bettingPro;
    protected $olbg;
    protected $tipstrr;
    protected $betway;
    protected $predictZPro;

    public function __construct(
        ForebetScraperService $forebet,
        BetExplorerScraperService $betExplorer,
        SoccerStatsScraperService $soccerStats,
        PredictZScraperService $predictZ,
        WinDrawWinScraperService $winDrawWin,
        BettingExpertScraperService $bettingExpert,
        SmartBetsScraperService $smartBets,
        BettingProScraperService $bettingPro,
        OLBGScraperService $olbg,
        TipstrrScraperService $tipstrr,
        BetwayScraperService $betway,
        PredictZProScraperService $predictZPro
    ) {
        $this->forebet = $forebet;
        $this->betExplorer = $betExplorer;
        $this->soccerStats = $soccerStats;
        $this->predictZ = $predictZ;
        $this->winDrawWin = $winDrawWin;
        $this->bettingExpert = $bettingExpert;
        $this->smartBets = $smartBets;
        $this->bettingPro = $bettingPro;
        $this->olbg = $olbg;
        $this->tipstrr = $tipstrr;
        $this->betway = $betway;
        $this->predictZPro = $predictZPro;
    }

    public function aggregateMatches()
    {
        // Scraper les données de chaque site
        // $forebetMatches = $this->forebet->scrape();
        $betExplorerMatches = $this->betExplorer->scrape();
        // $soccerStatsMatches = $this->soccerStats->scrape();
        // $predictZMatches = $this->predictZ->scrape();
        // $winDrawWinMatches = $this->winDrawWin->scrape();
        // $bettingExpertMatches = $this->bettingExpert->scrape();
        // $smartBetsMatches = $this->smartBets->scrape();
        // $bettingProMatches = $this->bettingPro->scrape();
        // $olbgMatches = $this->olbg->scrape();
        // $tipstrrMatches = $this->tipstrr->scrape();
        // $betwayMatches = $this->betway->scrape();
        // $predictZProMatches = $this->predictZPro->scrape();

        // Regrouper toutes les données
        $allMatches = [
            // 'Forebet' => $this->normalizeMatches($forebetMatches),
            'BetExplorer' => $this->normalizeMatches($betExplorerMatches),
            // 'SoccerStats' => $this->normalizeMatches($soccerStatsMatches),
            // 'PredictZ' => $this->normalizeMatches($predictZMatches),
            // 'WinDrawWin' => $this->normalizeMatches($winDrawWinMatches),
            // 'BettingExpert' => $this->normalizeMatches($bettingExpertMatches),
            // 'SmartBets' => $this->normalizeMatches($smartBetsMatches),
            // 'BettingPro' => $this->normalizeMatches($bettingProMatches),
            // 'OLBG' => $this->normalizeMatches($olbgMatches),
            // 'Tipstrr' => $this->normalizeMatches($tipstrrMatches), //null
            // 'Betway' => $this->normalizeMatches($betwayMatches), // null
            // ---------------------------------------------------------------------
            // 'PredictZPro' => $this->normalizeMatches($predictZProMatches),
        ];

        // Identifier les matchs communs
        $commonMatches = $this->getCommonMatches($allMatches);

        // Analyser les prédictions et fournir des recommandations
        return $this->analyzePredictions($commonMatches);
    }

    private function normalizeMatches($matches)
    {
        return collect($matches)->map(function ($match) {
            return [
                'teams' => strtolower(trim($match['teams'])),
                'data' => $match,
            ];
        })->groupBy('teams');
    }

    private function getCommonMatches($allMatches)
    {
        $commonMatches = [];

        foreach ($allMatches as $siteName => $siteMatches) {
            foreach ($siteMatches as $teams => $matchData) {
                if (!isset($commonMatches[$teams])) {
                    $commonMatches[$teams] = [];
                }
                $commonMatches[$teams][$siteName] = $matchData;
            }
        }

        // Retourner les matchs présents sur au moins 2 sites
        return collect($commonMatches)->filter(function ($sites) {
            return count($sites) > 1;
        });
    }

    private function analyzePredictions($commonMatches)
    {
        $analyzedMatches = [];

        foreach ($commonMatches as $teams => $matchData) {
            $predictions = [];
            $totalConfidence = 0;
            $count = 0;
            $recommendation = '';

            // Collecter les prédictions et confidences de chaque site
            foreach ($matchData as $site => $data) {
                $prediction = $data[0]['prediction'] ?? 'N/A';
                $confidence = $data[0]['confidence'] ?? '0';

                $predictions[$site] = $prediction;
                if (is_numeric($confidence)) {
                    $totalConfidence += (int) $confidence;
                    $count++;
                }
            }

            // Calculer la confiance moyenne
            $averageConfidence = $count > 0 ? $totalConfidence / $count : 0;

            // Déterminer la recommandation basée sur la confiance moyenne
            if ($averageConfidence > 80) {
                $recommendation = "Forte recommandation de parier sur le résultat prédit.";
            } elseif ($averageConfidence > 60) {
                $recommendation = "Recommandation modérée de parier sur le résultat prédit.";
            } else {
                $recommendation = "Il est risqué de parier sur ce match basé sur les données.";
            }

            $analyzedMatches[] = [
                'teams' => ucwords($teams),
                'predictions' => $predictions,
                'average_confidence' => round($averageConfidence, 2),
                'recommendation' => $recommendation,
            ];
        }

        return $analyzedMatches;
    }
}

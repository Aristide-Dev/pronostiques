<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ForebetScraperController extends Controller
{
    public function scrape()
    {
        // Définir le chemin complet vers le script
        $scriptPath = base_path('scrapeForebet.js');

        // Vérifier si le script existe
        if (!file_exists($scriptPath)) {
            return response()->json(['error' => 'Script de scraping non trouvé.'], 404);
        }

        // Créer le processus pour exécuter le script Node.js
        $process = new Process(['node', $scriptPath]);

        // Définir le répertoire de travail si nécessaire
        $process->setWorkingDirectory(base_path());


        // Exécuter le processus
        $process->run();
 
        // Vérifier si le processus a réussi
        if (!$process->isSuccessful()) {
            // Récupérer l'erreur et la retourner
            return response()->json(['error' => $process->getErrorOutput()], 500);
        }
        dd($process);

        // Récupérer la sortie JSON
        $output = $process->getOutput();
        $matches = json_decode($output, true);

        // Vérifier si la sortie est valide
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Erreur de décodage JSON: ' . json_last_error_msg()], 500);
        }

        // Assurer que toutes les chaînes sont encodées en UTF-8
        array_walk_recursive($matches, function (&$item, $key) {
            if (is_string($item)) {
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });

        return response()->json($matches);
    }
}

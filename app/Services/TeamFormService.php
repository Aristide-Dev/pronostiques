<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TeamFormService
{
    public function getRecentForm($teamName)
    {
       try {
         // Exemple avec une API fictive
         $response = Http::get("https://api-football.com/teams/{$teamName}/recent_form");

         if ($response->successful()) {
             return $response->json();
         }
         
        return null;
       } catch (\Throwable $th) {
        //throw $th;
        return null;
       }

    }
}

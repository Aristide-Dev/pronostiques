// app/pronostics/page.tsx
import MatchPrediction from './components/MatchPrediction';
import { Match } from './types';

const matches: Match[] = [
  {
    teams: 'Manchester United vs Chelsea',
    date: '2024-10-25',
    predictions: [
      { source: 'Source 1', prediction: 'Manchester United gagne', odds: 1.8 },
      { source: 'Source 2', prediction: 'Match nul', odds: 3.4 },
      { source: 'Source 3', prediction: 'Chelsea gagne', odds: 2.2 },
    ],
  },
  {
    teams: 'Real Madrid vs Barcelona',
    date: '2024-10-26',
    predictions: [
      { source: 'Source 1', prediction: 'Real Madrid gagne', odds: 2.1 },
      { source: 'Source 2', prediction: 'Barcelona gagne', odds: 2.3 },
      { source: 'Source 3', prediction: 'Match nul', odds: 3.5 },
    ],
  },
  // Ajoute d'autres matchs ici
];

export default function PronosticsPage() {
  return (
    <div className="container mx-auto py-8">
      <h1 className="text-4xl font-bold mb-6 text-center">Pronostics des matchs</h1>

      {matches.map((match, index) => (
        <MatchPrediction key={index} match={match} />
      ))}
    </div>
  );
}

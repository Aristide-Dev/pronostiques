// components/MatchPrediction.tsx
import { Match } from '../types';

interface MatchPredictionProps {
  match: Match;
}

const MatchPrediction: React.FC<MatchPredictionProps> = ({ match }) => {
  return (
    <div className="p-6 bg-white shadow-md rounded-md mb-6">
      <h2 className="text-2xl font-bold mb-4">{match.teams}</h2>
      <p className="text-gray-500 mb-2">Date: {match.date}</p>

      <table className="min-w-full bg-white">
        <thead>
          <tr>
            <th className="py-2 px-4 bg-gray-100 text-left">Source</th>
            <th className="py-2 px-4 bg-gray-100 text-left">Pronostic</th>
            <th className="py-2 px-4 bg-gray-100 text-left">Cote</th>
          </tr>
        </thead>
        <tbody>
          {match.predictions.map((prediction, index) => (
            <tr key={index} className="border-t">
              <td className="py-2 px-4">{prediction.source}</td>
              <td className="py-2 px-4">{prediction.prediction}</td>
              <td className="py-2 px-4">{prediction.odds}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default MatchPrediction;

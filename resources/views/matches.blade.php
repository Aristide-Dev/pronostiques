<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse des paris</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-6">Analyse des matchs communs</h1>

        @foreach($analyzedMatches as $match)
            <div class="bg-white shadow-md rounded mb-6 p-4">
                <h2 class="text-2xl font-semibold mb-2">{{ $match['teams'] }}</h2>
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Site</th>
                            <th class="py-2 px-4 border-b">Prédiction</th>
                            <th class="py-2 px-4 border-b">Confiance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($match['predictions'] as $site => $prediction)
                            <tr>
                                <td class="py-2 px-4 border-b">{{ $site }}</td>
                                <td class="py-2 px-4 border-b">{{ $prediction }}</td>
                                <td class="py-2 px-4 border-b">
                                    @if(isset($match['confidence']))
                                        {{ $match['confidence'] }}%
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="mt-4"><strong>Confiance moyenne :</strong> {{ $match['average_confidence'] }}%</p>
                <p class="mt-2"><strong>Recommandation :</strong> {{ $match['recommendation'] }}</p>
            </div>
        @endforeach

        <!-- Optionnel : Graphique des confiances moyennes -->
        <div class="bg-white shadow-md rounded p-4">
            <h2 class="text-2xl font-semibold mb-4">Confiance moyenne par match</h2>
            <canvas id="probabilityChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('probabilityChart').getContext('2d');
        const probabilityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($analyzedMatches as $match)
                        '{{ $match['teams'] }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Confiance moyenne (%)',
                    data: [
                        @foreach($analyzedMatches as $match)
                            {{ $match['average_confidence'] }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    </script>
</body>
</html>

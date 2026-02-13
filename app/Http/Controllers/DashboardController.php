<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $period = $request->string('period')->toString();
        if (!in_array($period, ['week', 'month', 'year'], true)) {
            $period = 'month';
        }

        $recommendationsQuery = Recommendation::query()->orderBy('created_at');
        if ($user->isDirecteur()) {
            $recommendationsQuery->where('responsible_unit', $user->direction ?? '__none__');
        }
        $recommendations = $recommendationsQuery->get();

        $statusTotals = [
            Recommendation::STATUS_APPLIED => 0,
            Recommendation::STATUS_LATE => 0,
            Recommendation::STATUS_NOT_APPLIED => 0,
            Recommendation::STATUS_NOT_DUE => 0,
        ];

        foreach ($recommendations as $recommendation) {
            $statusTotals[$recommendation->computed_status]++;
        }

        $byUnit = $recommendations
            ->groupBy('responsible_unit')
            ->map(function (Collection $items): array {
                $counts = [
                    Recommendation::STATUS_APPLIED => 0,
                    Recommendation::STATUS_LATE => 0,
                    Recommendation::STATUS_NOT_APPLIED => 0,
                    Recommendation::STATUS_NOT_DUE => 0,
                ];

                foreach ($items as $recommendation) {
                    $counts[$recommendation->computed_status]++;
                }

                $rate = $this->executionRate($counts);

                return [
                    'total' => $items->count(),
                    'appliquees' => $counts[Recommendation::STATUS_APPLIED],
                    'hors_delais' => $counts[Recommendation::STATUS_LATE],
                    'non_appliquees' => $counts[Recommendation::STATUS_NOT_APPLIED],
                    'non_echues' => $counts[Recommendation::STATUS_NOT_DUE],
                    'taux_execution' => $rate,
                ];
            })
            ->sortKeys();

        [$chartLabels, $chartValues] = $this->buildChartData($recommendations, $period);

        return view('dashboard.index', [
            'period' => $period,
            'statusTotals' => $statusTotals,
            'byUnit' => $byUnit,
            'overallRate' => $this->executionRate($statusTotals),
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
        ]);
    }

    private function executionRate(array $counts): float
    {
        $numerator = $counts[Recommendation::STATUS_APPLIED] ?? 0;
        $denominator = ($counts[Recommendation::STATUS_APPLIED] ?? 0)
            + ($counts[Recommendation::STATUS_NOT_APPLIED] ?? 0)
            + ($counts[Recommendation::STATUS_LATE] ?? 0);

        if ($denominator === 0) {
            return 0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    private function buildChartData(Collection $recommendations, string $period): array
    {
        $bucketFormat = match ($period) {
            'week' => 'o-\\WW',
            'year' => 'Y',
            default => 'Y-m',
        };

        $grouped = $recommendations
            ->groupBy(fn (Recommendation $recommendation): string => Carbon::parse($recommendation->created_at)->format($bucketFormat))
            ->sortKeys();

        $labels = [];
        $values = [];

        foreach ($grouped as $label => $items) {
            $counts = [
                Recommendation::STATUS_APPLIED => 0,
                Recommendation::STATUS_LATE => 0,
                Recommendation::STATUS_NOT_APPLIED => 0,
                Recommendation::STATUS_NOT_DUE => 0,
            ];

            foreach ($items as $recommendation) {
                $counts[$recommendation->computed_status]++;
            }

            $labels[] = $label;
            $values[] = $this->executionRate($counts);
        }

        return [$labels, $values];
    }
}

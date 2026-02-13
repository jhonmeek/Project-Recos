<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PerformanceController extends Controller
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

        $statusCounts = [
            Recommendation::STATUS_APPLIED => 0,
            Recommendation::STATUS_LATE => 0,
            Recommendation::STATUS_NOT_APPLIED => 0,
            Recommendation::STATUS_NOT_DUE => 0,
        ];

        foreach ($recommendations as $recommendation) {
            $statusCounts[$recommendation->computed_status]++;
        }

        [$trendLabels, $trendValues] = $this->buildExecutionTrend($recommendations, $period);

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

                return [
                    'total' => $items->count(),
                    'rate' => $this->executionRate($counts),
                ];
            })
            ->sortByDesc('rate')
            ->take(8);

        $lateItems = $recommendations->filter(
            fn (Recommendation $recommendation): bool => $recommendation->computed_status === Recommendation::STATUS_LATE
        );

        $avgDelayDays = round($lateItems->avg(function (Recommendation $recommendation): float {
            if (!$recommendation->due_date || !$recommendation->completion_date) {
                return 0;
            }

            return (float) $recommendation->completion_date->diffInDays($recommendation->due_date);
        }) ?? 0, 1);

        $onTimeNumerator = $statusCounts[Recommendation::STATUS_APPLIED];
        $onTimeDenominator = $statusCounts[Recommendation::STATUS_APPLIED] + $statusCounts[Recommendation::STATUS_LATE];
        $onTimeRate = $onTimeDenominator > 0
            ? round(($onTimeNumerator / $onTimeDenominator) * 100, 2)
            : 0;

        return view('performance.index', [
            'period' => $period,
            'kpis' => [
                'total' => $recommendations->count(),
                'execution_rate' => $this->executionRate($statusCounts),
                'on_time_rate' => $onTimeRate,
                'overdue_count' => $statusCounts[Recommendation::STATUS_NOT_APPLIED],
                'avg_delay_days' => $avgDelayDays,
            ],
            'trendLabels' => $trendLabels,
            'trendValues' => $trendValues,
            'statusLabels' => ['Appliquees', 'Hors delais', 'Non appliquees', 'Non echues'],
            'statusValues' => [
                $statusCounts[Recommendation::STATUS_APPLIED],
                $statusCounts[Recommendation::STATUS_LATE],
                $statusCounts[Recommendation::STATUS_NOT_APPLIED],
                $statusCounts[Recommendation::STATUS_NOT_DUE],
            ],
            'unitLabels' => $byUnit->keys()->values(),
            'unitValues' => $byUnit->pluck('rate')->values(),
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

    private function buildExecutionTrend(Collection $recommendations, string $period): array
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

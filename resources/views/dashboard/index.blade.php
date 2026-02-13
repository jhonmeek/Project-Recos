@extends('layouts.main')

@section('title', 'Statistiques')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold">Statistiques de suivi</h2>
        <p class="text-sm text-slate-500">Synthese par semaine, mois ou annee avec taux d'execution.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Total</p><p class="mt-2 text-2xl font-semibold">{{ array_sum($statusTotals) }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Appliquees</p><p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $statusTotals[\App\Models\Recommendation::STATUS_APPLIED] }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Hors delais</p><p class="mt-2 text-2xl font-semibold text-amber-700">{{ $statusTotals[\App\Models\Recommendation::STATUS_LATE] }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Taux global</p><p class="mt-2 text-2xl font-semibold">{{ number_format($overallRate, 2) }}%</p></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
            <label class="block text-sm font-medium text-slate-700">
                Synthese par
                <select name="period" class="mt-1 rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    <option value="week" @selected($period === 'week')>Semaine</option>
                    <option value="month" @selected($period === 'month')>Mois</option>
                    <option value="year" @selected($period === 'year')>Annee</option>
                </select>
            </label>
            <button class="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800" type="submit">Actualiser</button>
        </form>

        <h3 class="font-semibold">Evolution du taux d'execution</h3>
        <p class="mt-1 text-xs text-slate-500">Visualisation dynamique avec echelle normalisee, legende et infobulles.</p>
        <div class="mt-4 h-72 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <canvas
                id="execution-rate-chart"
                data-labels='@json($chartLabels)'
                data-values='@json($chartValues)'
            ></canvas>
        </div>

        <h3 class="mt-8 font-semibold">Detail par direction / fonction</h3>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Fonction</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Total</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Appliquees</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Hors delais</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Non appliquees</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Non echues</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Taux execution</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($byUnit as $unit => $stats)
                        <tr>
                            <td class="px-4 py-3">{{ $unit }}</td>
                            <td class="px-4 py-3">{{ $stats['total'] }}</td>
                            <td class="px-4 py-3">{{ $stats['appliquees'] }}</td>
                            <td class="px-4 py-3">{{ $stats['hors_delais'] }}</td>
                            <td class="px-4 py-3">{{ $stats['non_appliquees'] }}</td>
                            <td class="px-4 py-3">{{ $stats['non_echues'] }}</td>
                            <td class="px-4 py-3">{{ number_format($stats['taux_execution'], 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucune donnee disponible.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

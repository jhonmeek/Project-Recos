@extends('layouts.main')

@section('title', 'Graphiques & KPI')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold">Graphiques et indicateurs de performance</h2>
            <p class="text-sm text-slate-500">Lecture executive du suivi des recommandations DG.</p>
        </div>

        <form method="GET" class="flex items-end gap-2">
            <label class="block text-sm font-medium text-slate-700">
                Periode de tendance
                <select name="period" class="mt-1 rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    <option value="week" @selected($period === 'week')>Semaine</option>
                    <option value="month" @selected($period === 'month')>Mois</option>
                    <option value="year" @selected($period === 'year')>Annee</option>
                </select>
            </label>
            <button type="submit" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Actualiser</button>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wider text-slate-500">Volume total</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $kpis['total'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wider text-slate-500">Taux execution</p>
            <p class="mt-2 text-2xl font-semibold text-teal-700">{{ number_format($kpis['execution_rate'], 2) }}%</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wider text-slate-500">Respect des delais</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ number_format($kpis['on_time_rate'], 2) }}%</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wider text-slate-500">Non appliquees</p>
            <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $kpis['overdue_count'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wider text-slate-500">Retard moyen</p>
            <p class="mt-2 text-2xl font-semibold text-amber-700">{{ number_format($kpis['avg_delay_days'], 1) }} j</p>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h3 class="text-sm font-semibold text-slate-800">Evolution du taux d'execution</h3>
            <p class="text-xs text-slate-500">Objectif: suivre la dynamique dans le temps.</p>
            <div class="mt-4 h-80 rounded-lg border border-slate-200 bg-slate-50 p-3">
                <canvas
                    id="performance-trend-chart"
                    data-labels='@json($trendLabels)'
                    data-values='@json($trendValues)'
                ></canvas>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800">Repartition des statuts</h3>
            <p class="text-xs text-slate-500">Vue instantanee de l'etat global.</p>
            <div class="mt-4 h-80 rounded-lg border border-slate-200 bg-slate-50 p-3">
                <canvas
                    id="performance-status-chart"
                    data-labels='@json($statusLabels)'
                    data-values='@json($statusValues)'
                ></canvas>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800">Top directions par performance</h3>
        <p class="text-xs text-slate-500">Classement par taux d'execution.</p>
        <div class="mt-4 h-96 rounded-lg border border-slate-200 bg-slate-50 p-3">
            <canvas
                id="performance-unit-chart"
                data-labels='@json($unitLabels)'
                data-values='@json($unitValues)'
            ></canvas>
        </div>
    </div>
</div>
@endsection

@extends('layouts.main')

@section('title', 'Recommandations')

@section('content')
<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-lg font-semibold">Suivi des recommandations</h2>
            @if($user->isControleInterne())
                <a href="{{ route('recommendations.create') }}" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Nouvelle recommandation</a>
            @endif
        </div>

        <form method="GET" class="mt-5 grid gap-4 md:grid-cols-3">
            <label class="block text-sm font-medium text-slate-700">
                Statut
                <select name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Tous</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-medium text-slate-700">
                Responsable (RMO)
                <select name="responsible_unit" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Tous</option>
                    @foreach($unitOptions as $unit)
                        <option value="{{ $unit }}" @selected($unitFilter === $unit)>{{ $unit }}</option>
                    @endforeach
                </select>
            </label>

            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Filtrer</button>
                <a href="{{ route('recommendations.index') }}" class="rounded-md bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300">Reset</a>
            </div>
        </form>
    </div>

    @if($upcomingReminders->isNotEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800 shadow-sm">
            <h3 class="font-semibold">Rappels automatiques (J-3 a J-2)</h3>
            <ul class="mt-2 list-disc space-y-1 pl-6">
                @foreach($upcomingReminders as $reminder)
                    <li>#{{ $reminder->order_number }} - {{ $reminder->responsible_unit }} - echeance {{ $reminder->due_date->format('d/m/Y') }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">RMO</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Recommandation</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Priorite</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Echeance</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Statut</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Justificatifs</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($recommendations as $recommendation)
                    @php
                        $statusClass = match($recommendation->computed_status) {
                            \App\Models\Recommendation::STATUS_APPLIED => 'bg-emerald-100 text-emerald-700',
                            \App\Models\Recommendation::STATUS_LATE => 'bg-amber-100 text-amber-800',
                            \App\Models\Recommendation::STATUS_NOT_APPLIED => 'bg-rose-100 text-rose-700',
                            default => 'bg-blue-100 text-blue-700',
                        };
                    @endphp
                    @php
                        $priorityClass = match($recommendation->priority) {
                            \App\Models\Recommendation::PRIORITY_HIGH => 'bg-rose-100 text-rose-700',
                            \App\Models\Recommendation::PRIORITY_LOW => 'bg-slate-200 text-slate-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                    @endphp
                    <tr>
                        <td class="px-4 py-3">{{ $recommendation->order_number }}</td>
                        <td class="px-4 py-3">{{ $recommendation->responsible_unit }}</td>
                        <td class="px-4 py-3">{{ $recommendation->title }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $priorityClass }}">{{ $recommendation->priorityLabel() }}</span></td>
                        <td class="px-4 py-3">
                            @if($recommendation->is_immediate)
                                Immediate
                            @elseif($recommendation->due_date)
                                {{ $recommendation->due_date->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $recommendation->statusLabel() }}</span></td>
                        <td class="px-4 py-3">{{ $recommendation->evidences_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('recommendations.show', $recommendation) }}" class="rounded-md bg-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-300">Voir</a>
                                @if($user->isControleInterne() || $user->isDirecteur())
                                    <a href="{{ route('recommendations.edit', $recommendation) }}" class="rounded-md bg-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-300">Editer</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">Aucune recommandation pour le moment.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

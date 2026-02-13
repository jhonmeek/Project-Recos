@extends('layouts.main')

@section('title', 'Detail recommandation')

@section('content')
<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Recommandation #{{ $recommendation->order_number }}</h2>
            <div class="flex gap-2">
                @if($user->isControleInterne() || $user->isDirecteur())
                    <a href="{{ route('recommendations.edit', $recommendation) }}" class="rounded-md bg-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300">Editer</a>
                @endif
                <a href="{{ route('recommendations.index') }}" class="rounded-md bg-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300">Retour</a>
            </div>
        </div>

        <dl class="grid gap-4 md:grid-cols-2">
            <div><dt class="text-xs uppercase tracking-wide text-slate-500">RMO</dt><dd class="text-sm font-medium">{{ $recommendation->responsible_unit }}</dd></div>
            <div><dt class="text-xs uppercase tracking-wide text-slate-500">Statut</dt><dd class="text-sm font-medium">{{ $recommendation->statusLabel() }}</dd></div>
            <div><dt class="text-xs uppercase tracking-wide text-slate-500">Priorite</dt><dd class="text-sm font-medium">{{ $recommendation->priorityLabel() }}</dd></div>
            <div class="md:col-span-2"><dt class="text-xs uppercase tracking-wide text-slate-500">Libelle</dt><dd class="text-sm">{{ $recommendation->title }}</dd></div>
            <div><dt class="text-xs uppercase tracking-wide text-slate-500">Echeance</dt><dd class="text-sm">@if($recommendation->is_immediate)Immediate @elseif($recommendation->due_date){{ $recommendation->due_date->format('d/m/Y') }} @else - @endif</dd></div>
            <div><dt class="text-xs uppercase tracking-wide text-slate-500">Date de realisation</dt><dd class="text-sm">{{ $recommendation->completion_date?->format('d/m/Y') ?? 'Non renseignee' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-xs uppercase tracking-wide text-slate-500">Commentaire</dt><dd class="text-sm">{{ $recommendation->completion_note ?: 'Aucun commentaire.' }}</dd></div>
        </dl>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="font-semibold">Pieces justificatives</h3>
        </div>

        @if($recommendation->evidences->isEmpty())
            <p class="px-6 py-8 text-sm text-slate-500">Aucune piece jointe.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Nom</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recommendation->evidences as $evidence)
                            <tr>
                                <td class="px-4 py-3">{{ $evidence->original_name }}</td>
                                <td class="px-4 py-3">{{ $evidence->mime_type ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('recommendations.evidences.download', [$recommendation, $evidence]) }}" class="rounded-md bg-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-300">Telecharger</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

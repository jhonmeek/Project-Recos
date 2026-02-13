@php
    $isEdit = isset($recommendation);
    $isCreate = !$isEdit;
    $canManage = $user->isControleInterne();
@endphp

<div class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @if($canManage)
        <div class="grid gap-5 md:grid-cols-2">
            <label class="block text-sm font-medium text-slate-700">
                Numero d'ordre
                <input type="number" name="order_number" min="1" required
                    value="{{ old('order_number', $recommendation->order_number ?? $nextOrderNumber ?? 1) }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </label>

            <label class="block text-sm font-medium text-slate-700">
                Responsable (RMO)
                <select name="responsible_unit" required
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Selectionner un responsable</option>
                    @foreach($responsibleOptions as $option)
                        <option value="{{ $option }}" @selected(old('responsible_unit', $recommendation->responsible_unit ?? '') === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <label class="block text-sm font-medium text-slate-700">
            Libelle de la recommandation
            <textarea name="title" rows="4" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">{{ old('title', $recommendation->title ?? '') }}</textarea>
        </label>

        <label class="block text-sm font-medium text-slate-700">
            Priorite
            <select name="priority" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">Selectionner une priorite</option>
                @foreach($priorityOptions as $option)
                    <option value="{{ $option }}" @selected(old('priority', $recommendation->priority ?? '') === $option)>
                        {{ ucfirst($option) }}
                    </option>
                @endforeach
            </select>
        </label>

        <div class="grid gap-5 md:grid-cols-2">
            <label class="block text-sm font-medium text-slate-700">
                Echeance
                <input type="date" name="due_date" {{ $isCreate ? 'required' : '' }}
                    value="{{ old('due_date', isset($recommendation) && $recommendation->due_date ? $recommendation->due_date->format('Y-m-d') : '') }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </label>

            <label class="block text-sm font-medium text-slate-700">
                Date de realisation
                <input type="date" name="completion_date" {{ $isCreate ? 'required' : '' }}
                    value="{{ old('completion_date', isset($recommendation) && $recommendation->completion_date ? $recommendation->completion_date->format('Y-m-d') : '') }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </label>
        </div>
    @else
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Profil Directeur: vous pouvez uniquement renseigner l'avancement (realisation + justificatifs).
        </div>
    @endif

    <label class="block text-sm font-medium text-slate-700">
        Commentaire de realisation
        <textarea name="completion_note" rows="3" {{ $isCreate ? 'required' : '' }} class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">{{ old('completion_note', $recommendation->completion_note ?? '') }}</textarea>
    </label>

    <label class="block text-sm font-medium text-slate-700">
        Pieces justificatives
        <input type="file" name="evidence_files[]" multiple class="mt-1 block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-200 file:px-3 file:py-2 file:text-slate-700 hover:file:bg-slate-300">
    </label>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ $isEdit ? 'Mettre a jour' : 'Enregistrer' }}</button>
        <a href="{{ route('recommendations.index') }}" class="rounded-md bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300">Retour</a>
    </div>
</div>

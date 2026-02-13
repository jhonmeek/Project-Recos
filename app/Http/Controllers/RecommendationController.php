<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $statusFilter = $request->string('status')->toString();
        $unitFilter = $request->string('responsible_unit')->toString();

        $recommendationsQuery = Recommendation::withCount('evidences');

        if ($user->isDirecteur()) {
            $recommendationsQuery->where('responsible_unit', $user->direction ?? '__none__');
            $unitFilter = $user->direction ?? '';
        }

        $recommendations = $recommendationsQuery
            ->orderBy('order_number')
            ->get();

        if ($statusFilter !== '') {
            $recommendations = $recommendations->filter(
                fn (Recommendation $recommendation): bool => $recommendation->computed_status === $statusFilter
            )->values();
        }

        if ($unitFilter !== '' && !$user->isDirecteur()) {
            $recommendations = $recommendations->filter(
                fn (Recommendation $recommendation): bool => $recommendation->responsible_unit === $unitFilter
            )->values();
        }

        $upcomingRemindersQuery = Recommendation::query()
            ->whereNull('completion_date')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', now()->addDays(2)->toDateString())
            ->whereDate('due_date', '<=', now()->addDays(3)->toDateString())
            ->orderBy('due_date');

        if ($user->isDirecteur()) {
            $upcomingRemindersQuery->where('responsible_unit', $user->direction ?? '__none__');
        }

        $upcomingReminders = $upcomingRemindersQuery->get();

        $unitOptions = $user->isDirecteur()
            ? collect([$user->direction])->filter()
            : Recommendation::query()
                ->select('responsible_unit')
                ->distinct()
                ->orderBy('responsible_unit')
                ->pluck('responsible_unit');

        return view('recommendations.index', [
            'recommendations' => $recommendations,
            'upcomingReminders' => $upcomingReminders,
            'statusOptions' => [
                Recommendation::STATUS_APPLIED => 'Appliquees',
                Recommendation::STATUS_LATE => 'Appliquees hors delais',
                Recommendation::STATUS_NOT_APPLIED => 'Non appliquees',
                Recommendation::STATUS_NOT_DUE => 'Non echues',
            ],
            'unitOptions' => $unitOptions,
            'statusFilter' => $statusFilter,
            'unitFilter' => $unitFilter,
            'user' => $user,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->isControleInterne(), 403);
        $nextOrderNumber = (int) Recommendation::query()->max('order_number') + 1;

        return view('recommendations.create', [
            'nextOrderNumber' => $nextOrderNumber,
            'responsibleOptions' => $this->responsibleOptions(),
            'priorityOptions' => $this->priorityOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isControleInterne(), 403);
        $responsibleOptions = $this->responsibleOptions();
        $validated = $request->validate([
            'order_number' => ['required', 'integer', 'min:1', 'unique:recommendations,order_number'],
            'responsible_unit' => ['required', 'string', Rule::in($responsibleOptions)],
            'title' => ['required', 'string'],
            'priority' => ['required', 'string', Rule::in($this->priorityOptions())],
            'due_date' => ['required', 'date'],
            'completion_date' => ['required', 'date'],
            'completion_note' => ['required', 'string'],
            'evidence_files.*' => ['nullable', 'file', 'max:8192'],
        ]);

        $recommendation = Recommendation::create([
            'order_number' => $validated['order_number'],
            'responsible_unit' => $validated['responsible_unit'],
            'title' => $validated['title'],
            'priority' => $validated['priority'],
            'is_immediate' => false,
            'due_date' => $validated['due_date'],
            'completion_date' => $validated['completion_date'],
            'completion_note' => $validated['completion_note'],
        ]);

        $this->storeEvidenceFiles($request, $recommendation);

        return redirect()
            ->route('recommendations.show', $recommendation)
            ->with('success', 'Recommandation enregistree avec succes.');
    }

    public function show(Recommendation $recommendation): View
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->canAccessDirection($recommendation->responsible_unit), 403);
        $recommendation->load('evidences');

        return view('recommendations.show', [
            'recommendation' => $recommendation,
            'user' => $user,
        ]);
    }

    public function edit(Recommendation $recommendation): View
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->canAccessDirection($recommendation->responsible_unit), 403);
        $recommendation->load('evidences');
        abort_unless($user?->isControleInterne() || $user?->isDirecteur(), 403);

        return view('recommendations.edit', [
            'recommendation' => $recommendation,
            'user' => $user,
            'responsibleOptions' => $this->responsibleOptions(),
            'priorityOptions' => $this->priorityOptions(),
        ]);
    }

    public function update(Request $request, Recommendation $recommendation): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user?->isControleInterne() || $user?->isDirecteur(), 403);
        abort_unless($user->canAccessDirection($recommendation->responsible_unit), 403);

        if ($user->isControleInterne()) {
            $responsibleOptions = $this->responsibleOptions();
            $validated = $request->validate([
                'order_number' => ['required', 'integer', 'min:1', 'unique:recommendations,order_number,' . $recommendation->id],
                'responsible_unit' => ['required', 'string', Rule::in($responsibleOptions)],
                'title' => ['required', 'string'],
                'priority' => ['required', 'string', Rule::in($this->priorityOptions())],
                'is_immediate' => ['nullable', 'boolean'],
                'due_date' => ['nullable', 'date', 'required_without:is_immediate'],
                'completion_date' => ['nullable', 'date'],
                'completion_note' => ['nullable', 'string'],
                'evidence_files.*' => ['nullable', 'file', 'max:8192'],
            ]);

            $recommendation->update([
                'order_number' => $validated['order_number'],
                'responsible_unit' => $validated['responsible_unit'],
                'title' => $validated['title'],
                'priority' => $validated['priority'],
                'is_immediate' => (bool) ($validated['is_immediate'] ?? false),
                'due_date' => $validated['due_date'] ?? null,
                'completion_date' => $validated['completion_date'] ?? null,
                'completion_note' => $validated['completion_note'] ?? null,
            ]);
        } else {
            $validated = $request->validate([
                'completion_date' => ['nullable', 'date'],
                'completion_note' => ['nullable', 'string'],
                'evidence_files.*' => ['nullable', 'file', 'max:8192'],
            ]);

            $recommendation->update([
                'completion_date' => $validated['completion_date'] ?? null,
                'completion_note' => $validated['completion_note'] ?? null,
            ]);
        }

        $this->storeEvidenceFiles($request, $recommendation);

        return redirect()
            ->route('recommendations.show', $recommendation)
            ->with('success', 'Recommandation mise a jour.');
    }

    public function destroy(Recommendation $recommendation): RedirectResponse
    {
        abort_unless(auth()->user()?->isControleInterne(), 403);
        foreach ($recommendation->evidences as $evidence) {
            Storage::disk('local')->delete($evidence->file_path);
        }

        $recommendation->delete();

        return redirect()
            ->route('recommendations.index')
            ->with('success', 'Recommandation supprimee.');
    }

    public function downloadEvidence(Recommendation $recommendation, Evidence $evidence)
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->canAccessDirection($recommendation->responsible_unit), 403);
        abort_unless($evidence->recommendation_id === $recommendation->id, 404);

        return Storage::disk('local')->download($evidence->file_path, $evidence->original_name);
    }

    private function storeEvidenceFiles(Request $request, Recommendation $recommendation): void
    {
        if (!$request->hasFile('evidence_files')) {
            return;
        }

        foreach ($request->file('evidence_files') as $file) {
            $path = $file->store('evidences', 'local');

            $recommendation->evidences()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function responsibleOptions(): array
    {
        $defaults = [
            'Accueil et securite',
            'Charge d\'etudes 1',
            'Charge d\'etudes 2',
            'Conseiller 1',
            'Conseiller 2',
            'DAF',
            'DS',
            'DSIC',
        ];

        $fromDb = Recommendation::query()
            ->select('responsible_unit')
            ->distinct()
            ->orderBy('responsible_unit')
            ->pluck('responsible_unit')
            ->filter()
            ->values()
            ->all();

        $options = array_values(array_unique(array_merge($defaults, $fromDb)));
        sort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $options;
    }

    /**
     * @return array<int, string>
     */
    private function priorityOptions(): array
    {
        return [
            Recommendation::PRIORITY_HIGH,
            Recommendation::PRIORITY_MEDIUM,
            Recommendation::PRIORITY_LOW,
        ];
    }
}

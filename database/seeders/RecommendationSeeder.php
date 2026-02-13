<?php

namespace Database\Seeders;

use App\Models\Recommendation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'DAF',
            'DS',
            'DSIC',
            'Conseiller 1',
            'Conseiller 2',
            'Charge d\'etudes 1',
            'Charge d\'etudes 2',
            'Accueil et securite',
        ];

        $subjects = [
            'Produire le rapport de suivi budgetaire',
            'Actualiser la matrice des risques',
            'Faire le point des dossiers en attente',
            'Lancer la campagne d\'information etudiants',
            'Consolider les statistiques mensuelles',
            'Organiser une reunion de coordination',
            'Verifier la conformite des procedures',
            'Mettre a jour la liste des beneficiaires',
            'Transmettre les justificatifs au Controle interne',
            'Finaliser le compte-rendu de mission',
        ];

        $rows = [];
        $order = 1;

        // Donnees historiques sur 8 mois pour alimenter les stats semaine/mois/annee.
        for ($monthOffset = 8; $monthOffset >= 1; $monthOffset--) {
            $base = now()->subMonths($monthOffset)->startOfMonth();

            foreach ($units as $unitIndex => $unit) {
                for ($i = 0; $i < 3; $i++) {
                    $created = $base->copy()->addDays(($unitIndex * 2 + $i * 4) % 24 + 1);
                    $due = $created->copy()->addDays(8 + (($unitIndex + $i) % 10));

                    $statusPattern = ($order % 10);
                    $completionDate = null;
                    $isImmediate = false;

                    if (in_array($statusPattern, [1, 2, 3, 4], true)) {
                        // Appliquee dans les delais.
                        $completionDate = $due->copy()->subDays(rand(0, 3));
                    } elseif (in_array($statusPattern, [5, 6], true)) {
                        // Appliquee hors delais.
                        $completionDate = $due->copy()->addDays(rand(1, 6));
                    } elseif (in_array($statusPattern, [7, 8], true)) {
                        // Non appliquee (echeance depassee).
                        $completionDate = null;
                    } else {
                        // Quelques recommandations immediates.
                        $isImmediate = true;
                        $due = null;
                        $completionDate = $created->copy()->addDays(rand(0, 2));
                    }

                    $rows[] = [
                        'order_number' => $order,
                        'responsible_unit' => $unit,
                        'title' => $subjects[array_rand($subjects)] . ' - lot ' . $order,
                        'due_date' => $due?->toDateString(),
                        'is_immediate' => $isImmediate,
                        'completion_date' => $completionDate?->toDateString(),
                        'completion_note' => $completionDate ? 'Execution constatee et tracee.' : null,
                        'created_at' => $created->toDateTimeString(),
                        'updated_at' => $created->toDateTimeString(),
                    ];

                    $order++;
                }
            }
        }

        // Donnees du mois courant avec des recommandations non echues.
        foreach ($units as $unitIndex => $unit) {
            for ($i = 0; $i < 2; $i++) {
                $created = now()->startOfMonth()->addDays(($unitIndex + 1) * 2 + $i);
                $due = Carbon::now()->addDays(5 + $unitIndex + $i);

                $rows[] = [
                    'order_number' => $order,
                    'responsible_unit' => $unit,
                    'title' => 'Action prioritaire en cours - lot ' . $order,
                    'due_date' => $due->toDateString(),
                    'is_immediate' => false,
                    'completion_date' => null,
                    'completion_note' => null,
                    'created_at' => $created->toDateTimeString(),
                    'updated_at' => $created->toDateTimeString(),
                ];

                $order++;
            }
        }

        Recommendation::query()->insert($rows);
    }
}

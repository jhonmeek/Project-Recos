<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    public const STATUS_APPLIED = 'appliquee';
    public const STATUS_LATE = 'hors_delais';
    public const STATUS_NOT_APPLIED = 'non_appliquee';
    public const STATUS_NOT_DUE = 'non_echue';
    public const PRIORITY_HIGH = 'haute';
    public const PRIORITY_MEDIUM = 'moyenne';
    public const PRIORITY_LOW = 'basse';

    protected $fillable = [
        'order_number',
        'responsible_unit',
        'title',
        'priority',
        'due_date',
        'is_immediate',
        'completion_date',
        'completion_note',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completion_date' => 'date',
        'is_immediate' => 'boolean',
    ];

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    public function computeStatus(?Carbon $referenceDate = null): string
    {
        $reference = ($referenceDate ?? now())->startOfDay();

        if ($this->completion_date !== null) {
            if ($this->effective_due_date !== null && $this->completion_date->gt($this->effective_due_date)) {
                return self::STATUS_LATE;
            }

            return self::STATUS_APPLIED;
        }

        if ($this->effective_due_date === null || $this->effective_due_date->gte($reference)) {
            return self::STATUS_NOT_DUE;
        }

        return self::STATUS_NOT_APPLIED;
    }

    public function statusLabel(): string
    {
        return match ($this->computed_status) {
            self::STATUS_APPLIED => 'Appliquee',
            self::STATUS_LATE => 'Appliquee hors delais',
            self::STATUS_NOT_APPLIED => 'Non appliquee',
            default => 'Non echue',
        };
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            self::PRIORITY_HIGH => 'Haute',
            self::PRIORITY_LOW => 'Basse',
            default => 'Moyenne',
        };
    }

    protected function computedStatus(): Attribute
    {
        return Attribute::get(fn (): string => $this->computeStatus());
    }

    protected function effectiveDueDate(): Attribute
    {
        return Attribute::get(function (): ?Carbon {
            if ($this->due_date !== null) {
                return $this->due_date->copy()->startOfDay();
            }

            return $this->is_immediate ? $this->created_at?->copy()->startOfDay() : null;
        });
    }
}

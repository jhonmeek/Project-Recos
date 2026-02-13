<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_DG = 'dg';
    public const ROLE_CONTROLE_INTERNE = 'controle_interne';
    public const ROLE_DIRECTEUR = 'directeur';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'direction',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isControleInterne(): bool
    {
        return $this->role === self::ROLE_CONTROLE_INTERNE;
    }

    public function isDirecteur(): bool
    {
        return $this->role === self::ROLE_DIRECTEUR;
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_DG => 'DG',
            self::ROLE_CONTROLE_INTERNE => 'Controle interne',
            default => 'Directeur',
        };
    }

    public function canAccessDirection(?string $responsibleUnit): bool
    {
        if (!$this->isDirecteur()) {
            return true;
        }

        return $this->direction !== null && $this->direction === $responsibleUnit;
    }
}

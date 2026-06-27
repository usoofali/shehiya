<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'state_id',
        'lga_id',
        'ward_id',
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

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function hasJurisdictionOver(?int $stateId, ?int $lgaId, ?int $wardId): bool
    {
        if ($this->hasRole(['Super Administrator', 'National Administrator'])) {
            return true;
        }

        if ($this->hasRole('State Coordinator')) {
            return $this->state_id && $this->state_id === $stateId;
        }

        if ($this->hasRole('LGA Coordinator')) {
            return $this->lga_id && $this->lga_id === $lgaId;
        }

        if ($this->hasRole('Ward Coordinator')) {
            return $this->ward_id && $this->ward_id === $wardId;
        }

        return false;
    }
}

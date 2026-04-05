<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'active',
        'requires_guard_day',
        'admin_user_id',
    ];

    protected function casts(): array
    {
        return [
            'active'             => 'boolean',
            'requires_guard_day' => 'boolean',
        ];
    }

    // ── Administrador principal (FK directa) ──────────────────────────────

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    // ── Auxiliares (múltiples, tabla pivot) ───────────────────────────────

    public function auxiliaries(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'group_auxiliaries',
            'group_id',
            'user_id'
        )->withTimestamps();
    }

    // ── Miembros del grupo (tabla pivot group_user) ───────────────────────

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'group_user',
            'group_id',
            'user_id'
        )->withTimestamps();
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeRequiresGuardDay($query)
    {
        return $query->where('requires_guard_day', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function needsGuardDay(): bool
    {
        return (bool) $this->requires_guard_day;
    }
}

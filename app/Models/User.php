<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        // * Datos personales adicionales
        'last_name',
        'second_last_name',
        'gender',
        'marital_status',
        'guard_day',
        'recommendation_letter',
        'birth_date',
        'phone',
        'address',
        'join_date',
        'observations',
        'photo',
    ];

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
            'birth_date'           => 'date',
            'join_date'            => 'date',
            'recommendation_letter' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // El usuario puede acceder al panel si tiene algún rol que NO sea solo 'registrado'
        return $this->roles()->where('name', '!=', 'registrado')->exists() || $this->hasRole('super_admin');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->photo ? Storage::disk('private')->url($this->photo) : null;
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')->withTimestamps();
    }

    public function administeredGroups()
    {
        return $this->hasMany(Group::class, 'admin_user_id');
    }

    public function auxiliaryGroups()
    {
        return $this->hasMany(Group::class, 'aux_user_id');
    }

    public function requieresGuardDay()
    {
        return $this->groups()->where('requires_guard_day', true)->exists();
    }

    public function getDisplayNameAttribute()
    {
        return $this->full_name ?? $this->name;
    }

    public static function genderOptions(): array
    {
        return [
            'male'   => 'Masculino',
            'female' => 'Femenino',
            'other'  => 'Otro',
        ];
    }

    public static function maritalStatusOptions(): array
    {
        return [
            'single'   => 'Soltero/a',
            'married'  => 'Casado/a',
            'divorced' => 'Divorciado/a',
            'widowed'  => 'Viudo/a',
            'other'    => 'Otro',
        ];
    }
}

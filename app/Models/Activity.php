<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'requires_guard_day',
        'active',
    ];

    protected $casts = [
        'requires_guard_day' => 'boolean',
        'active'             => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'activity_user')
            ->using(ActivityUser::class)
            ->withPivot(['date_add_to_user_activity', 'recommendation_letter'])
            ->withTimestamps();
    }

    public function activityUsers()
    {
        return $this->hasMany(ActivityUser::class, 'activity_id');
    }
}

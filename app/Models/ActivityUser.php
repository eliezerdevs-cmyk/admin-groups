<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ActivityUser extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     * Required by Filament Repeater for pivot models.
     */
    public $incrementing = true;

    protected $table = 'activity_user';

    protected $fillable = [
        'user_id',
        'activity_id',
        'date_add_to_user_activity',
        'recommendation_letter',
    ];

    protected function casts(): array
    {
        return [
            'date_add_to_user_activity' => 'date',
            'recommendation_letter'     => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterestedPerson extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'report_id',
        'name',
        'last_name',
        'address',
        'colony',
        'theme',
        'notes',
        'status',
        'is_candidate',
    ];

    protected $casts = [
        'status' => 'string',
        'is_candidate' => 'boolean',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}

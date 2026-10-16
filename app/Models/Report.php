<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'day',
        'group_id',
        'user_id',
        'date',
        'street_start',
        'streets_traveled',
        'street_end',
        'houses_visited',
        'houses_opened',
        'houses_didnotopen',
        'candidates_baptism',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'houses_visited' => 'integer',
        'houses_opened' => 'integer',
        'houses_didnotopen' => 'integer',
        'candidates_baptism' => 'integer',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interestedPeople()
    {
        return $this->hasMany(InterestedPerson::class);
    }

    public function totalHouses()
    {
        return $this->houses_visited + $this->houses_didnotopen;
    }
    
}

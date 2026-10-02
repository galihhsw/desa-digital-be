<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use SoftDeletes, UUID;

    protected $fillable = [
        'thumbnail',
        'name',
        'description',
        'price',
        'date',
        'time',
        'is_active',
    ]; 

    protected $casts = [
        'price' => 'decimal:2'
    ];
    
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%'. $search. '%')
            ->orWhere('date', 'like', '%'. $search. '%')
            ->orWhere('description', 'like', '%'. $search. '%');
    }


    public function eventParticipants()
    {
        return $this->hasMany(EventParticipant::class);
    }
}

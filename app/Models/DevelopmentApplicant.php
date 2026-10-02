<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DevelopmentApplicant extends Model
{
    use SoftDeletes, UUID, HasFactory;

    protected $fillable = [
        'development_id',
        'user_id',
        'status',
    ];

    public function development()
    {
        return $this->belongsTo(Development::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%");
            $query->orWhere('status', 'like', "%{$search}%");
        });
    }
}

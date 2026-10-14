<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SocialAssistanceRecipient extends Model
{
    use SoftDeletes, UUID, HasFactory;

    protected $fillable = [
        'social_assistance_id',
        'head_of_family_id',
        'amount',
        'reason',
        'bank',
        'account_number',
        'proof',
        'status',
    ];

    public function scopeSearch($query, $search)
    {
        return $query->whereHas('headOfFamily', function ($query) use ($search){
            $query->whereHas('user', function ($query) use ($search){
                $query->where('name', 'like', '%'. $search. '%');
                $query->orWhere('email', 'like', '%'. $search. '%');
            });
        });
    }

    public function socialAssistance()
    {
        return $this->belongsTo(SocialAssistance::class);
    }

    public function headOfFamily()
    {
        return $this->belongsTo(HeadOfFamily::class)->withTrashed();
    }
}

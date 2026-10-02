<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use SoftDeletes, UUID;

    protected $fillable = [
        'thumbnail',
        'name',
        'about',
        'headman',
        'people',
        'agricultural_area',
        'total_area',
    ];

    protected $casts = [
        'people' => 'integer',
        'agricultural_area' => 'float',
        'total_area' => 'float',
    ];

    public function profileImages()
    {
        return $this->hasMany(ProfileImage::class);
    }
}

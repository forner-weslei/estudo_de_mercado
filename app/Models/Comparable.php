<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comparable extends Model
{
    protected $fillable = [
        'study_id',
        'title','address',
        'price','area_m2',
        'bedrooms','suites','parking','bathrooms',
        'include_in_calc',
        'main_photo_path',
        'sort_order'
    ];

    protected $casts = [
        'include_in_calc' => 'boolean',
        'price' => 'float',
        'area_m2' => 'float',
    ];

    public function study(): BelongsTo { return $this->belongsTo(Study::class); }

    public function getPricePerM2Attribute()
    {
        return $this->area_m2 > 0 ? $this->price / $this->area_m2 : 0;
    }

    public function getMainPhotoUrlAttribute()
    {
        return $this->main_photo_path ? asset('storage/'.$this->main_photo_path) : null;
    }
}

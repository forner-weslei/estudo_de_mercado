<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brand extends Model
{
    protected $fillable = [
        'user_id',
        'company_name','agent_name','creci','phone','whatsapp','email','website','footer_text',
        'color_primary','color_secondary',
        'logo_path'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}

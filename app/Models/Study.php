<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Study extends Model
{
    protected $fillable = [
        'user_id',
        'owner_name','owner_contact',
        'subject_address','subject_neighborhood','subject_city','subject_state',
        'subject_type',
        'subject_area_m2','subject_bedrooms','subject_suites','subject_parking',
        'notes',
        'scenario_base','manual_total_price',
        'pct_optimistic','pct_market','pct_competitive',
        'override_branding',
        'brand_company_name','brand_agent_name','brand_creci','brand_phone','brand_whatsapp','brand_email','brand_website','brand_footer_text',
        'brand_color_primary','brand_color_secondary','brand_logo_path'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function comparables(): HasMany { return $this->hasMany(Comparable::class); }

    public function isLand(): bool
    {
        return ($this->subject_type ?? 'house') === 'land';
    }

    public function areaLabel(): string
    {
        return $this->isLand() ? 'Área do terreno' : 'Área privativa';
    }

    public function effectiveBrand()
    {
        if ($this->override_branding) {
            return (object)[
                'company_name' => $this->brand_company_name,
                'agent_name' => $this->brand_agent_name,
                'creci' => $this->brand_creci,
                'phone' => $this->brand_phone,
                'whatsapp' => $this->brand_whatsapp,
                'email' => $this->brand_email,
                'website' => $this->brand_website,
                'footer_text' => $this->brand_footer_text,
                'color_primary' => $this->brand_color_primary ?: '#0B2C4A',
                'color_secondary' => $this->brand_color_secondary ?: '#C9A227',
                'logo_url' => $this->brand_logo_path ? asset('storage/'.$this->brand_logo_path) : null
            ];
        }
        $b = $this->user->brand;
        return (object)[
            'company_name' => $b?->company_name ?? 'Minha Imobiliária',
            'agent_name' => $b?->agent_name ?? 'Corretor',
            'creci' => $b?->creci,
            'phone' => $b?->phone,
            'whatsapp' => $b?->whatsapp,
            'email' => $b?->email,
            'website' => $b?->website,
            'footer_text' => $b?->footer_text,
            'color_primary' => $b?->color_primary ?? '#0B2C4A',
            'color_secondary' => $b?->color_secondary ?? '#C9A227',
            'logo_url' => $b?->logo_path ? asset('storage/'.$b->logo_path) : null
        ];
    }

    public function computePricing(): array
    {
        $items = $this->comparables->where('include_in_calc', true);
        $count = max(1, $items->count());

        $avgTotal = $items->avg('price') ?? 0;
        $avgM2 = $items->avg(function($c){ return $c->area_m2 > 0 ? $c->price / $c->area_m2 : 0; }) ?? 0;
        $lowestTotal = $items->min('price') ?? 0;

        $baseTotal = match($this->scenario_base) {
            'avg_total' => $avgTotal,
            'avg_m2' => $avgM2 * $this->subject_area_m2,
            'lowest_total' => $lowestTotal,
            'manual_total' => (float)($this->manual_total_price ?? 0),
            default => $avgTotal
        };

        $scenarios = [
            'optimistic' => $baseTotal * (1 + ($this->pct_optimistic/100)),
            'market' => $baseTotal * (1 + ($this->pct_market/100)),
            'competitive' => $baseTotal * (1 + ($this->pct_competitive/100)),
        ];

        return [
            'avg_total' => (float)$avgTotal,
            'avg_m2' => (float)$avgM2,
            'lowest_total' => (float)$lowestTotal,
            'base_total' => (float)$baseTotal,
            'scenarios' => $scenarios,
            'scenarios_m2' => [
                'optimistic' => $this->subject_area_m2 > 0 ? $scenarios['optimistic'] / $this->subject_area_m2 : 0,
                'market' => $this->subject_area_m2 > 0 ? $scenarios['market'] / $this->subject_area_m2 : 0,
                'competitive' => $this->subject_area_m2 > 0 ? $scenarios['competitive'] / $this->subject_area_m2 : 0,
            ],
        ];
    }

    public function isLand(): bool
    {
        return ($this->subject_type ?? '') === 'land';
    }
}

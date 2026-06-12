<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FeatureToggle extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
        'target_role',
        'activation_date',
        'expiration_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'activation_date' => 'datetime',
        'expiration_date' => 'datetime',
    ];

    /**
     * Scope a query to only include currently active features.
     * Takes into account is_active flag and date ranges.
     */
    public function scopeCurrentlyActive($query)
    {
        $now = Carbon::now();

        return $query->where('is_active', true)
                     ->where(function ($q) use ($now) {
                         $q->whereNull('activation_date')
                           ->orWhere('activation_date', '<=', $now);
                     })
                     ->where(function ($q) use ($now) {
                         $q->whereNull('expiration_date')
                           ->orWhere('expiration_date', '>=', $now);
                     });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Services extends Model
{
    /** @use HasFactory<\Database\Factories\ServicesFactory> */
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'description',
        'price_min',
        'price_max',
        'is_active',
        'sort_order',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}

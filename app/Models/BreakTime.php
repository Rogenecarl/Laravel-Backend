<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    /**
     * Get the provider that owns the break time.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
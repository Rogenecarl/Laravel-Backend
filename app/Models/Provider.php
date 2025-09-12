<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    /** @use HasFactory<\Database\Factories\ProviderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'verified_by',
        'healthcare_name',
        'description',
        'phone_number',
        'email',
        'status',
        'address',
        'city',
        'province',
        'latitude',
        'longitude',
        'verified_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Services::class);
    }

    public function operatingHours(): HasMany
    {
        return $this->hasMany(OperatingHour::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

}

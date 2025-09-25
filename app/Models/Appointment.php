<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'appointment_number',
        'user_id',
        'provider_id',
        'start_time',
        'end_time',
        'status',
        'notes',
        'total_price',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the user (patient) who booked the appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider for this appointment.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * The services that belong to the appointment.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Services::class, 'appointment_service', 'appointment_id', 'service_id')
            ->withPivot('price_at_booking')
            ->withTimestamps();
    }
}

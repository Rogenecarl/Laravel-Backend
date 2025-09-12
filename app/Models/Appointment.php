<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'user_id',
        'provider_id',
        'bookable_id',
        'bookable_type',
        'start_time',
        'end_time',
        'status',
        'notes',
        'price_at_booking',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'reminder_sent',
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
     * ADDED: This is an essential relationship.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the parent bookable model (can be a Service or a Package).
     * ADDED: This is the polymorphic relationship.
     */
    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }
}

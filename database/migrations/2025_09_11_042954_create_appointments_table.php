<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
             $table->id();
            $table->string('appointment_number')->unique();

            // --- The "Who" ---
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // The patient
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');

            // --- The "What" (Polymorphic Relationship) ---
            // This is the key to booking both Services and Packages.
            // It replaces the old 'service_id'.
            $table->morphs('bookable'); // This automatically adds:
                                       // - unsignedBigInteger('bookable_id')
                                       // - string('bookable_type')

            // --- The "When" ---
            // Storing both start and end times is far more efficient for queries.
            $table->dateTime('start_time');
            $table->dateTime('end_time');

            // --- The "Details" ---
            $table->enum('status', [
                'pending', 'confirmed', 'completed', 'cancelled', 'no_show'
            ])->default('pending');
            $table->text('notes')->nullable(); // From the patient during booking

            // --- The "Price" ---
            // This is critical. It stores the final price at the moment of booking.
            // It's not nullable because every appointment MUST have a price.
            $table->decimal('price_at_booking', 10, 2);

            // --- The "Tracking" ---
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->boolean('reminder_sent')->default(false);

            $table->timestamps();

            // --- The "Indexes" for Performance ---
            $table->index(['provider_id', 'start_time', 'end_time']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

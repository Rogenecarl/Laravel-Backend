<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('break_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');

            // --- The "What" ---
            $table->string('name')->default('Lunch Break'); // e.g., "Lunch", "Admin Time"

            // --- The "When" ---
            // This defines a recurring break for a specific day of the week.
            $table->unsignedTinyInteger('day_of_week'); // (0 = Sunday, 6 = Saturday)
            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_times');
    }
};

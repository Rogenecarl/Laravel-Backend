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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('healthcare_name');
            $table->text('description')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
             $table->string('cover_photo')->nullable();
            $table->enum('status', ['pending', 'verified', 'suspended', 'rejected'])->default('pending');

                   // Location fields
            $table->string('address');
            $table->string('city')->default('Digos');
            $table->string('province')->default('Davao del Sur');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'category_id', 'status',]);
            $table->index(['healthcare_name', 'city', 'province']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};

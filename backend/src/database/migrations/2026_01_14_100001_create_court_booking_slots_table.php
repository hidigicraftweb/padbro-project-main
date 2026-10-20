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
        Schema::create('court_booking_slots', function (Blueprint $table) {
            $table->id();
            $table->dateTime('slot_at'); // start datetime of slot
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['locked', 'booked'])->default('locked');
            $table->dateTime('expires_at')->nullable(); // for locked slots
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['court_id', 'slot_at'], 'court_booking_slots_unique');
            $table->index('booking_id');
            $table->index(['court_id', 'slot_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_booking_slots');
    }
};

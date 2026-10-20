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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->integer('total_hours');
            $table->bigInteger('price_per_hour_amount'); // snapshot from court price (IDR)
            $table->bigInteger('sub_total_amount'); // price_per_hour * total_hours (IDR)
            $table->bigInteger('total_tax_amount')->default(0); // 11% tax (IDR)
            $table->bigInteger('grand_total_amount'); // sub_total + tax (IDR)
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('proof_of_payment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('court_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

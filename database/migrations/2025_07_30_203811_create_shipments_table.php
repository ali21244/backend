<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
              $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('tracking_number')->unique();
        $table->string('description');
        $table->string('country');
        $table->string('origin');
        $table->string('destination');
        $table->string('status')->default('in_transit');
        $table->float('weight');
        $table->decimal('price', 8, 2)->nullable();
        $table->date('estimated_delivery')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
              $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('tracking_number')->unique();
        $table->string('description');
        $table->string('country');
        $table->string('origin_country');
        $table->string('destination_country');
        $table->float('weight');
        $table->string('status')->default('in_transit');
        $table->date('estimated_delivery')->nullable();
        $table->string('shipping_method')->nullable();
        $table->decimal('price', 8, 2)->nullable();
        $table->boolean('insurance')->default(false);
        $table->string('discount_applied')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};

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
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('branch')->default(0); 
            $table->bigInteger('biometric_category_id')->default(0); 
            $table->string('name')->nullable(); 
            $table->string('ip_address')->nullable(); 
            $table->string('port')->nullable(); 
            $table->tinyInteger('status')->default(0); 
            $table->timestamp('last_sync')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_devices');
    }
};

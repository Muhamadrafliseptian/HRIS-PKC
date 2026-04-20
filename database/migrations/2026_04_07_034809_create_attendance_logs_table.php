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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
        
            $table->string('user_id');
            $table->timestamp('scan_time');
        
            $table->bigInteger('branch')->nullable();
            $table->bigInteger('device_id')->nullable();
        
            $table->string('device_ip')->nullable();
        
            $table->timestamps();
        
            $table->index(['user_id', 'scan_time']);
            $table->index(['branch', 'scan_time']);
            $table->index(['device_id', 'scan_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};

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
        Schema::create('branch_configs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('branch');
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('time_zone', 255);
            $table->string('time_zone_label', 255);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_configs');
    }
};

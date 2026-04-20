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
        Schema::create('shift_details', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('shift_id');

            $table->time('clock_in');
            $table->time('clock_out');

            $table->boolean('is_cross_day')->default(0);

            $table->integer('order')->default(1);

            // tolerance per segment (lebih fleksibel)
            $table->integer('tolerance_before_in')->nullable();
            $table->integer('tolerance_after_in')->nullable();
            $table->integer('tolerance_before_out')->nullable();
            $table->integer('tolerance_after_out')->nullable();

            // minimal kerja biar dianggap hadir
            $table->integer('min_work_minutes')->nullable();

            // future proof (misal break)
            $table->enum('segment_type', ['work', 'break'])->default('work');

            $table->timestamps();

            $table->unique(['shift_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

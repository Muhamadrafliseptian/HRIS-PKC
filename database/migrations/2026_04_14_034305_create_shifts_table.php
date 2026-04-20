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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique(); // S01, S02, OFF, dll
            $table->string('name');

            $table->bigInteger('branch')->nullable();
            $table->bigInteger('shift_category')->nullable();

            $table->enum('type', ['single', 'double', 'split'])->default('single');

            // tolerance dalam menit
            $table->integer('tolerance_before_in')->nullable();
            $table->integer('tolerance_after_in')->nullable();
            $table->integer('tolerance_before_out')->nullable();
            $table->integer('tolerance_after_out')->nullable();

            $table->integer('total_work_minutes')->nullable();

            $table->boolean('is_off')->default(0);
            $table->boolean('is_active')->default(1);
            $table->boolean('is_default')->default(0);

            $table->text('description')->nullable();

            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};

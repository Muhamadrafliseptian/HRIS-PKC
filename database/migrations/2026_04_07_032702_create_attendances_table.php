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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee');
            $table->bigInteger('branch')->nullable();
            $table->bigInteger('shift_id')->nullable();

            $table->date('date');

            $table->timestamp('first_scan_at')->nullable();
            $table->timestamp('last_scan_at')->nullable();

            $table->integer('total_work_minutes')->default(0);

            $table->integer('late_minutes')->default(0);
            $table->integer('early_out_minutes')->default(0);

            $table->enum('status', ['present', 'late', 'absent', 'partial'])
                ->default('present');

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['employee', 'shift_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

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
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
        
            $table->bigInteger('employee_id');
            $table->date('date');
        
            $table->bigInteger('shift_id');
            $table->bigInteger('branch')->nullable();
        
            // snapshot shift saat itu (biar histori aman 🔥)
            $table->json('shift_snapshot')->nullable();
        
            // penanda libur nasional
            $table->boolean('is_holiday')->default(0);
        
            // sumber data
            $table->enum('source', ['manual', 'generate', 'import'])->default('manual');
        
            $table->text('notes')->nullable();
        
            $table->timestamps();
        
            $table->unique(['employee_id', 'date']);
            $table->index(['employee_id', 'date']);
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

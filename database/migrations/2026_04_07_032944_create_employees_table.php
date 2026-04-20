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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unique(); 
            $table->bigInteger('employee_status')->nullable(); 
            $table->string('employee_number')->nullable();
            $table->string('name')->nullable();
            $table->string('nrk')->nullable();
            $table->string('nik')->nullable();
            $table->string('phone', 255)->nullable();
            $table->bigInteger('religion')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('birth_place', 255)->nullable();
            $table->enum('blood', ['A', 'B', 'AB', '0', '-'])->nullable();
            $table->bigInteger('branch')->nullable();
            $table->bigInteger('klaster')->nullable();
            $table->bigInteger('division')->nullable();
            $table->bigInteger('job_title')->nullable();
            $table->bigInteger('position')->nullable();
            $table->bigInteger('dependent')->nullable();
            $table->string('ter', 255)->nullable();
            $table->boolean('sync')->default(0);
            $table->boolean('status')->default(1);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

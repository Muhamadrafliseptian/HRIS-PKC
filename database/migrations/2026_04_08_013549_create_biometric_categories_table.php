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
        Schema::create('biometric_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nama kategori, misal ASN / NON ASN');
            $table->text('description')->nullable()->comment('Keterangan tambahan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_categories');
    }
};
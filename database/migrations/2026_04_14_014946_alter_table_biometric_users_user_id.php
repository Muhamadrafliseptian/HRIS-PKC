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
        Schema::table('biometric_users', function (Blueprint $table) {
            $table->dropUnique('biometric_users_uid_unique');
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('uid')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('biometric_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->unique()->change();
            $table->unsignedBigInteger('uid')->unique()->change();
        });
    }
};

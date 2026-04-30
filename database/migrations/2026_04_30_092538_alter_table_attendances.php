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
        //
        Schema::table('attendances', function (Blueprint $table) {
            $table->bigInteger('in_device_id')->nullable()->after('first_scan_at');
            $table->bigInteger('out_device_id')->nullable()->after('last_scan_at');
            $table->bigInteger('in_branch')->nullable()->after('in_device_id');
            $table->bigInteger('out_branch')->nullable()->after('out_device_id');
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

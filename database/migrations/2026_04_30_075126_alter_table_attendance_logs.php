<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('device_id');
            $table->index('scan_time');
            $table->index('branch');
            $table->unique(
                ['user_id', 'device_id', 'scan_time', 'branch'],
                'attendance_logs_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('attendance_logs_unique');
            $table->dropIndex(['branch']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['device_id']);
            $table->dropIndex(['scan_time']);
        });
    }
};
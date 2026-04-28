<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('biometric_users', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('uid');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('device_id');

            $table->string('name');
            $table->tinyInteger('role')->default(0);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->unique(['device_id', 'user_id']);
            $table->unique(['device_id', 'uid']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('biometric_users');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_timelogs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->timestamp('logs');
            $table->string('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_timelogs');
    }
};

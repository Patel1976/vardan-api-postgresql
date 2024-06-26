<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_users', function (Blueprint $table) {
          $table->id();
          $table->uuid('uuid');
          $table->string('name');
          $table->string('email')->nullable();
          $table->string('phone')->unique();
          $table->string('mpin')->nullable();
          $table->string('address');
          $table->boolean('status')->nullable();
          $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('staff_users');
    }
};

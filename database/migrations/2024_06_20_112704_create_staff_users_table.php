<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\StaffUser;

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
            $table->string('mpin');
            $table->string('address');
            $table->boolean('status');
            $table->timestamps();
        });

        StaffUser::create([
            'name' => 'Staff1',
            'email' => 'staff@mail.com',

        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_users');
    }
};

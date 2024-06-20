<?php

use App\Models\SystemModule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('action')->nullable();
            $table->string('slug')->nullable();
            $table->string('icon')->nullable();
            $table->integer('parent_module_id')->nullable();
            $table->boolean('is_permissible')->nullable();
            $table->boolean('status')->nullable();
            $table->integer('display_order')->nullable();
            $table->timestamps();
        });
        $modules = [
            ['name' => 'Dashboard', 'action' => null, 'slug' => 'dashboard' , 'icon' => 'bx bx-home side-menu__icon' , 'parent_module_id' => null , 'is_permissible' => false , 'status' => true , 'display_order' => 1],
            ['name' => 'User Managment', 'action' => null, 'slug' => null , 'icon' => 'bi bi-person-lines-fill me-2' , 'parent_module_id' => null , 'is_permissible' => false , 'status' => true , 'display_order' => 2],
            ['name' => 'Manage Users', 'action' =>json_encode(['view','edit','create','delete']), 'slug' => 'users' , 'icon' => null , 'parent_module_id' => 2 , 'is_permissible' => true , 'status' => true , 'display_order' => 3],
            ['name' => 'Manage Roles', 'action' => json_encode(['view','edit','create','delete']), 'slug' => 'roles' , 'icon' => null , 'parent_module_id' => 2 , 'is_permissible' => true , 'status' => true , 'display_order' => 4],
            ['name' => 'Settings', 'action' => null, 'slug' => null , 'icon' => 'bi bi-gear me-2' , 'parent_module_id' => null , 'is_permissible' => false , 'status' => true , 'display_order' => 5],
            ['name' => 'Email Template', 'action' =>json_encode(['view','edit','create','delete']), 'slug' => 'email-template' , 'icon' => null , 'parent_module_id' => 5 , 'is_permissible' => true , 'status' => true , 'display_order' => 6],
        ];
            SystemModule::insert($modules);
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_modules');
    }
};

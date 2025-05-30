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
            ['name' => 'Dashboard', 'action' => null, 'slug' => 'dashboard' , 'icon' => 'tachometer-alt' , 'parent_module_id' => null , 'is_permissible' => false , 'status' => true , 'display_order' => 1],
            ['name' => 'User Managment', 'action' => null, 'slug' => null , 'icon' => 'users' , 'parent_module_id' => null , 'is_permissible' => false , 'status' => true , 'display_order' => 2],
            ['name' => 'Manage Users', 'action' =>json_encode(['view','edit','create','delete']), 'slug' => 'users' , 'icon' => null , 'parent_module_id' => 2 , 'is_permissible' => true , 'status' => true , 'display_order' => 3],
            ['name' => 'Staff Managment', 'action' => null, 'slug' => 'null' , 'icon' => 'user-tie' , 'parent_module_id' => null , 'is_permissible' => false , 'status' => true , 'display_order' => 4],
            ['name' => 'Manage Staff', 'action' => json_encode(['view','edit','create','delete']), 'slug' => 'staff' , 'icon' => null , 'parent_module_id' => 4 , 'is_permissible' => true , 'status' => true , 'display_order' => 5],
            ['name' => 'Work Journey', 'action' => json_encode(['view','edit','create','delete']), 'slug' => 'workjourney' , 'icon' => null , 'parent_module_id' => 4 , 'is_permissible' => true , 'status' => true , 'display_order' => 6],
            ['name' => 'Reports', 'action' => null, 'slug' => null , 'icon' => 'exclamation-triangle' , 'parent_module_id' => null , 'is_permissible' => false , 'status' => true , 'display_order' => 7],
            ['name' => 'Time Logs', 'action' => json_encode(['view','edit','create','delete']), 'slug' => 'timelogs' , 'icon' => null , 'parent_module_id' => 7 , 'is_permissible' => true , 'status' => true , 'display_order' => 8],
            ['name' => 'Emergency Logs', 'action' => json_encode(['view','edit','create','delete']), 'slug' => 'emergency-logs' , 'icon' => null , 'parent_module_id' => 7 , 'is_permissible' => true , 'status' => true , 'display_order' => 9],
            ['name' => 'Settings', 'action' => null, 'slug' => null , 'icon' => 'cog' , 'parent_module_id' => null , 'is_permissible' => false , 'status' => true , 'display_order' => 10],
            ['name' => 'Email Template', 'action' =>json_encode(['view','edit','create','delete']), 'slug' => 'email-template' , 'icon' => null , 'parent_module_id' => 10 , 'is_permissible' => true , 'status' => true , 'display_order' => 11],
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

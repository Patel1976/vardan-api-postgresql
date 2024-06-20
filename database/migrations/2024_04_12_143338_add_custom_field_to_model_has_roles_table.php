<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomFieldToModelHasRolesTable extends Migration
{
    public function up()
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->json('module')->nullable(); 
        });
    }

    public function down()
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropColumn('module'); 
        });
    }
}
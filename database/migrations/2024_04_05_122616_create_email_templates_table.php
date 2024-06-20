<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->timestamps();
        });
        EmailTemplate::create([
            "name" => "FORGET_PASSWORD",
            "subject" => "Forget Password Email",
            "body" => "<p>Hi [NAME],</p><p>There was a request to change your password!</p><p>If you did not make this request then please ignore this email.</p><p>Otherwise, please click this link to change your password:</p><p><a href='[PASSWORD_RESET_LINK]\' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none;\'>Reset Password</a></p><p>Or copy the password reset link into your browser: [DOMAIN]</p><p>Thanks,</p>"
        ]);
        EmailTemplate::create([
            "name" => "CHANGE_PASSWORD",
            "subject" => "change Password Email",
            "body" => "<p>Hi [NAME],</p><p>There was a request to change your password!</p><p>If you did not make this request then please ignore this email.</p><p>Otherwise, please click this link to change your password:</p><p><a href='[CHANGE_PASSWORD_LINK]\' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none;\'>Change Password</a></p><p>Or copy the change password link into your browser: [DOMAIN]</p><p>Thanks,</p>"
        ]);
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};

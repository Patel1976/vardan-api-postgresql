<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffEmergencyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_logs',
    ];
}

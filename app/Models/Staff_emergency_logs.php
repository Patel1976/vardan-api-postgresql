<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff_emergency_logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_path',
        'description',
    ];
}

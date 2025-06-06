<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffTimelog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'logs', 'type',
    ];

    public function staffUser()
    {
        return $this->belongsTo(StaffUser::class, 'user_id');
    }
}

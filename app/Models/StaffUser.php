<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StaffUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'mpin',
        'address',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function images()
{
    return $this->hasMany(Staff_emergency_logs::class);
}

}


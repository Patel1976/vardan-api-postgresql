<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class StaffUser extends Model implements JWTSubject
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'mpin',
        'otp',
        'address',
        'status',
        'department',
        'image',
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
    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}


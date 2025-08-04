<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSession extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'device_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone',
        'device_id',
        'device_name',
        'last_login_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the staff user that owns the device session.
     */
    public function staffUser()
    {
        return $this->belongsTo(StaffUser::class, 'phone', 'phone');
    }
}
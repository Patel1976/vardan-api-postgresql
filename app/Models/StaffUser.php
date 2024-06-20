<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffUser extends Model
{
    use HasFactory;
<<<<<<< HEAD
    
    protected $fillable = [
      'name', 'email', 'phone', 'mpin', 'address', 'status'
=======
    protected $fillable = [
        'name', 'email', 'phone', 'mpin', 'address', 'status',
>>>>>>> 00263de050f31c649602bdde946f139f1fb3f2e2
    ];
}

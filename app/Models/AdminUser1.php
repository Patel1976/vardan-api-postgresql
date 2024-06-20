<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUser1 extends Model
{
  protected $fillable = [
    'name',
    'email',
    'password',
    'status',
    'phone',
    'token',
    'token_created_at'
];
    use HasFactory;
}

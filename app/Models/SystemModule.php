<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'action' , 'slug', 'icon', 'parent_module_id', 'is_permissible', 'status', 'display_order'];
    
    public function subModules()
    {
        return $this->hasMany(SystemModule::class, 'parent_module_id');
    }   
}

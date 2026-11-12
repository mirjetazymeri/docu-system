<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLevel extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'access_level_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_access_levels');
    }
}

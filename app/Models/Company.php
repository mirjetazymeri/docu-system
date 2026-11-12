<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['name', 'description', 'email', 'phone_number', 'website'];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
    public function audits()
    {
        return $this->hasMany(Audit::class);
    } 
}

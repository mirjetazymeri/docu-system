<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'branch_id', 'name', 'description', 'head'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function workplaces()
    {
        return $this->hasMany(Workplace::class);
    }

}

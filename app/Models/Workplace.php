<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workplace extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['department_id', 'name', 'location'];


    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'company_id', 'name', 'location', 'manager'
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
    public function audits()
    {
        return $this->hasMany(Audit::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'documentation_status',
        'documentation_completed_at',
        'documentation_completed_by',
    ];

    protected $casts = [
        'documentation_completed_at' => 'datetime',
    ];

    // Define the relationship with AppCategory
    public function category()
    {
        return $this->belongsTo(AppCategory::class);
    }

    // Define the relationship with Unit
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function manuals()
    {
        return $this->hasMany(Manual::class);
    }

    public function softwareCompanyDetails()
    {
        return $this->hasMany(SoftwareCompanyDetail::class);
    }

    public function applicationTestings()
    {
        return $this->hasMany(ApplicationTesting::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'application_user')
            ->withPivot('can_edit')
            ->withTimestamps();
    }
}

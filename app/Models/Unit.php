<?php

// app/Models/Unit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'application_id']; // Fields that are mass assignable

    // Define the relationship with Application
    // public function application()
    // {
    //     return $this->belongsTo(Application::class);
    // }

        // Define the relationship with Application
        public function application()
        {
            return $this->belongsTo(Application::class);
        }

        // Define the relationship with Documentation
        public function documentations()
        {
            return $this->hasMany(Documentation::class);
        }


        // Relationship to ApplicationTestingDetail
        public function testingDetails()
        {
            return $this->hasMany(ApplicationTestingDetail::class);
        }
}

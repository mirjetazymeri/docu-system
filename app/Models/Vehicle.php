<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'make', 'model', 'year', 'vin', 'price', 'color', 'registration_number'
    ];

    public function services()
    {
        return $this->hasMany(VehicleService::class);
    }

    public function insurances()
    {
        return $this->hasMany(VehicleInsurance::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleService extends Model
{
    use HasFactory;


    protected $fillable = [
        'vehicle_id', 'service_date', 'service_km', 'description', 'cost', 'next_service_date', 'next_service_km'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

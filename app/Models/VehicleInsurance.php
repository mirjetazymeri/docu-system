<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'provider', 'policy_number', 'start_date', 'end_date', 'cost'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

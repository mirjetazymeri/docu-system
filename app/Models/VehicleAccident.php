<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleAccident extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'employee_id', 'accident_date', 'description', 'damage_cost', 'location'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetsArea extends Model
{
    // Define table if it differs from the default
    protected $table = 'assets_areas';

    // Define fillable columns (assuming these fields are mass assignable)
    protected $fillable = [
        'asset_name',
        'device_type_id',
        'brand_id',
        'company_id',
        'branch_id',
        'department_id',
        'workplace_id',
        'serial_number',
        'cpu',
        'ram',
        'storage',
        'gpu',
    ];

    // Define relationships (assuming you've set up these relationships)
    public function deviceType()
    {
        return $this->belongsTo(DeviceType::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function workplace()
    {
        return $this->belongsTo(Workplace::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareCompany extends Model
{
    use HasFactory;

    protected $table = 'software_company'; // Make sure the table name is correct
    protected $fillable = ['name', 'application_id'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function details()
    {
        return $this->hasMany(SoftwareCompanyDetail::class);
    }
}

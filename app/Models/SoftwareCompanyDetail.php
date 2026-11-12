<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareCompanyDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'software_company_id', 'category','module', 'feature',  'result', 'comment'
    ];

    public function softwareCompany()
    {
        return $this->belongsTo(SoftwareCompany::class);
    }
}


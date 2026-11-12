<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $fillable = [
        'title', 'created_date', 'company_id', 'branch_id', 'content', 'audit_title_id'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function auditTitle()
    {
        return $this->belongsTo(AuditTitle::class, 'audit_title_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'contract_type', 'start_date', 'end_date', 'salary', 'details', 'closed_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('end_date')->orWhere('end_date', '>', now());
        })->whereNull('closed_at');
    }

    public function close()
    {
        $this->update(['closed_at' => now()]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'comments',
        'rating'
    ];

    // Define relationship with Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // // Define relationship with Employee as the reviewer
    // public function reviewer()
    // {
    //     return $this->belongsTo(Employee::class, 'reviewer_id');
    // }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}

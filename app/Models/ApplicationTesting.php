<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationTesting extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'tester_name',
        'test_date',
        'version',
        'is_completed',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'test_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];
    // Relationship to Application
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    // Relationship to ApplicationTestingDetail
    public function testingDetails()
    {
        return $this->hasMany(ApplicationTestingDetail::class);
    }
}

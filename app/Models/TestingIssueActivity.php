<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestingIssueActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_testing_detail_id',
        'user_id',
        'event',
        'comment',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function testingDetail()
    {
        return $this->belongsTo(
            ApplicationTestingDetail::class,
            'application_testing_detail_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

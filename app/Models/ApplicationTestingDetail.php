<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationTestingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_testing_id',
        'sequence_number',
        'unit_id', // For the module (Unit)
        'test_case',
        'result',
        'comment',
        'category',
        'fix_status',
        'reported_by',
        'reported_at',
        'fix_comment',
        'fixed_by',
        'fixed_at',
        'reviewed_by',
        'reviewed_at',
        'review_comment',
    ];

    protected $casts = [
        'sequence_number' => 'integer',
        'reported_at' => 'datetime',
        'fixed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relationship to ApplicationTesting
    public function applicationTesting()
    {
        return $this->belongsTo(ApplicationTesting::class);
    }

    // Relationship to Unit (Module)
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function fixedBy()
    {
        return $this->belongsTo(User::class, 'fixed_by');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function activities()
    {
        return $this->hasMany(
            TestingIssueActivity::class,
            'application_testing_detail_id'
        )->latest('occurred_at');
    }

    public function getReferenceAttribute(): string
    {
        $number = $this->sequence_number;

        /*
         * Legacy fallback for environments where the sequence migration has
         * not yet backfilled this row. Use its position inside the testing
         * record, never the global database ID.
         */
        if (!$number && $this->exists && $this->application_testing_id) {
            $number = static::query()
                ->where(
                    'application_testing_id',
                    $this->application_testing_id
                )
                ->where('id', '<=', $this->id)
                ->count();
        }

        $number = $number ?: 1;

        return 'TC-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }
}

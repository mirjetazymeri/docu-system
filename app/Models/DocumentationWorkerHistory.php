<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentationWorkerHistory extends Model
{
    protected $fillable = [
        'user_id',
        'application_id',
        'unit_id',
        'documentation_id',
        'event',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function documentation()
    {
        return $this->belongsTo(Documentation::class);
    }
}

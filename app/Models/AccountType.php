<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(AccountType::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccountType::class, 'parent_id');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'is_buyer',
        'is_supplier',
        'is_both',
        'nui',
        'contact_person',
    ];

    public function isBuyer()
    {
        return $this->is_buyer;
    }

    public function isSupplier()
    {
        return $this->is_supplier;
    }

    public function isBoth()
    {
        return $this->is_both;
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}

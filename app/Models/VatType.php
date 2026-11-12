<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VatType extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'rate'];

    public function expenseTypes()
    {
        return $this->hasMany(ExpenseType::class);
    }

    public function item()
    {
        return $this->hasMany(Item::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'vat_type_id'];

    public function vatType()
    {
        return $this->belongsTo(VatType::class);
    }

    // public function expenses()
    // {
    //     return $this->belongsToMany(Expense::class);
    // }

    public function expenses()
    {
        return $this->belongsToMany(Expense::class, 'expense_expense_type')
                    ->withPivot('amount', 'price_without_vat', 'price_with_vat', 'vat_rate')
                    ->withTimestamps();
    }
}

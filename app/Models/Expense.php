<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $guarded = [];

    // public function expenseTypes()
    // {
    //     return $this->belongsToMany(ExpenseType::class, 'expense_expense_type')
    //                 ->withPivot('amount', 'price_without_vat', 'price_with_vat', 'vat_rate', 'employee_id', 'vehicle_id', 'vat_code')
    //                 ->withTimestamps();
    // }

    public function expenseTypes()
    {
        return $this->belongsToMany(ExpenseType::class, 'expense_expense_type')
                    ->withPivot(
                        'amount',
                        'price_per_unit_without_vat',
                        'price_per_unit_with_vat',
                        'price_without_vat',
                        'price_with_vat',
                        'total_vat_amount',
                        'vat_rate',
                        'vat_code',
                        'employee_id',
                        'vehicle_id',
                        'discount1',
                        'discount2',
                        'discount3'
                    )->withTimestamps();
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'item_id',
        'branch_id',
        'quantity',
        'price_with_vat',
        'price_without_vat',
        'vat_rate',
        'vat_code',
        'account_id',
        'has_lot_number',
        'lot_number',
        'expiration_date',
        'discount1',
        'discount2',
        'discount3',
        'accounted',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}

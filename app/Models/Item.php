<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'item_type_id',
        'vat_type_id',
        'is_for_sale',
        'account_id',
        'has_lot_number',
        'has_expiration_date',
    ];

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function vatType()
    {
        return $this->belongsTo(VatType::class);
    }

    public function branchStocks()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }



}

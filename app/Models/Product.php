<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
    ];

    // products → purchase_items (1:N)
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
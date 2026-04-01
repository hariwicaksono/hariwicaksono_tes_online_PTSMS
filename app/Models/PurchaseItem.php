<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'qty',
        'price',
    ];

    // purchase_items → purchases (N:1)
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    // purchase_items → products (N:1)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Products;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        "quantity"
    ];

    
    /**
     * Get the User of Cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product of cart
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_group_items', "product_id", "group_id");
    }
}

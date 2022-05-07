<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\UserProductGroups;

class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "price"
    ];

    /**
     * Get the User of product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Groups of Product
     * MTM relationship
     */
    public function groups()
    {
        return $this->belongsToMany(UserProductGroups::class, 'product_group_items', "product_id", "group_id");
    }

    /**
     * Get the Cart of Product.
     * Gets all rows from cart table where is store product id
     */
    public function cart()
    {
        return $this->hasMany(Cart::class, "product_id");
    }
}

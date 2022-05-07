<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\ProductGroupItems;
use App\Models\Products;

class UserProductGroups extends Model
{
    use HasFactory;
    
    protected $fillable = [
        "discount"
    ];

    /**
     * Get the User of product group
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    

    /**
     * Get the Prodcuts of group
     * MTM relationship
     */
    public function products()
    {
        return $this->belongsToMany(Products::class, 'product_group_items', "group_id", "product_id");
    }
}

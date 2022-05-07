<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Products;
use App\Models\Cart;
use App\Models\UserProductGroups;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the Products of User.
     */
    public function products()
    {
        return $this->hasMany(Products::class);
    }

    /**
     * Get the Product groupss of User.
     */
    public function userProductGroups()
    {
        return $this->hasMany(UserProductGroups::class);
    }

    /**
     * Get the Cart of User.
     */
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }
}

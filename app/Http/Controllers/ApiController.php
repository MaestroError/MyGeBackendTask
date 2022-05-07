<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Cart;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function addProductInCart(Request $request) {
        $user = auth()->user();
        $product_id = $request->product_id;
        if (!$user->cart()->where("product_id", $product_id)->exists()) {
            $cart = new Cart();
            $cart->user_id = $user->id;
            $cart->product_id = $product_id;
            $cart->save();
        };
        return response([
            'status' => 200,
            'message' => 'Product added to cart'
        ], 200); 
    }

    public function removeProductFromCart(Request $request) {
        $user = auth()->user();
        $product_id = $request->product_id;
        $user->cart()->where("product_id", $product_id)->delete();
        return response([
            'status' => 200,
            'message' => 'Product removed from cart'
        ], 200); 
    }

    public function setCartProductQuantity(Request $request) {
        $user = auth()->user();
        $product_id = $request->product_id;
        $quantity = $request->quantity;

        $cartRow = $user->cart()->where("product_id", $product_id)->first();
        $cartRow->quantity = $quantity;
        $cartRow->save();

        return response([
            'status' => 200,
            'message' => 'Product removed from cart'
        ], 200); 
    }

    public function getUserCart(Request $request) {
        $user = auth()->user();
        $cart = $user->cart()->with("products")->get();
        $groups = [];
        // set prices in cart and get existing groups
        $cart->map(function ($item) {
            $item->price = $item->products->price;
            return $item;
        });

        foreach ($cart as $item) {
            $group = $item->products()->first()->groups()->first();
            if($group) {
                $groups[] = $group;
            }
        }


        // set data
        $data["products"] = $cart;
        $data['discount'] = round(0, 2);

        // search for allowed groups
        $allowedGroups = [];
        $cartProductIds = $cart->pluck("product_id");

        if(!empty($groups)) {
            foreach ($groups as $group) {
                $groupids = $group->products()->get()->pluck("id");
                $diff = $groupids->diff($cartProductIds);
                if($diff->isEmpty()) {
                    $allowedGroups[] = [
                        "group_id" => $group->id,
                        "ids" => $groupids,
                        "discount" => $group->discount
                    ];
                }
                
            }
        }

        // check allowedGroups and apply discount
        if(!empty($allowedGroups)) {
            $groupsUsed = [];

            $newDiscount = 0.0;
            foreach ($allowedGroups as $group) {
                if(!in_array($group['group_id'], $groupsUsed)) {
                    $discountPerc = 0;
                    $minQuantity = 1000;
                    foreach ($cart as $item) {
                        if(in_array($item->product_id, $group["ids"]->toArray())) {
                            // calculate min minQuantity
                            $minQuantity = $minQuantity < $item->quantity ? $minQuantity : $item->quantity;
                            $discountPerc = $group['discount'];
                        }
                    }
                    foreach ($cart as $item) {
                        if(in_array($item->product_id, $group["ids"]->toArray())) {
                            // calculate new discount
                            $newDiscount = $newDiscount + $this->getDiscount($item->price, $discountPerc, $minQuantity);
                        }
                    }
                    $groupsUsed[] = $group['group_id'];
                }
            }
            $data['discount'] = round($newDiscount, 2);
        }

        // dd($data);
        return response($data, 200); 
    }

    protected function getDiscount(int $price, int $percent, int $minAmount): float
    {
        return round(($price*$percent)/100 * $minAmount, 2);
    }
}

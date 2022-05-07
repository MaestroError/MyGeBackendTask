<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiController extends Controller
{
    /**
     * Adds product in cart
     * POST product_id
     *
     * @param Request $request
     * @return Response
     */
    public function addProductInCart(Request $request): Response
    {
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

    /**
     * Removes product from cart
     * POST product_id
     *
     * @param Request $request
     * @return Response
     */
    public function removeProductFromCart(Request $request): Response
    {
        $user = auth()->user();
        $product_id = $request->product_id;
        $user->cart()->where("product_id", $product_id)->delete();
        return response([
            'status' => 200,
            'message' => 'Product removed from cart'
        ], 200); 
    }
    /**
     * Updates product's quantity in cart
     * POST product_id, quantity
     *
     * @param Request $request
     * @return Response
     */
    public function setCartProductQuantity(Request $request): Response
    {
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

    /**
     * Returns user cart with product details and discount
     *
     * @param Request $request
     * @return Response
     */
    public function getUserCart(Request $request) {
        $user = auth()->user();
        $cart = $user->cart()->with("products")->get();
        
        // set prices in cart
        $cart->map(function ($item) {
            $item->price = $item->products->price;
            return $item;
        });

        // set data
        $data["products"] = $cart;
        $data['discount'] = 0.00;
        
        // Get existing groups
        $groups = [];
        foreach ($cart as $item) {
            $group = $item->products()->first()->groups()->first();
            if($group) {
                $groups[] = $group;
            }
        }

        // search for allowed groups
        $allowedGroups = [];
        $cartProductIds = $cart->pluck("product_id");

        if(!empty($groups)) {
            foreach ($groups as $group) {
                // get products ids and find difference with cart products ids
                $groupids = $group->products()->get()->pluck("id");
                $diff = $groupids->diff($cartProductIds);
                // if there is no difference, it is allowed
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
                // Check the group for not be used again
                if(!in_array($group['group_id'], $groupsUsed)) {
                    $discountPerc = 0;
                    $minQuantity = 1000;
                    // count min quantity and get discount percent
                    foreach ($cart as $item) {
                        // check if product belongs to this group
                        if(in_array($item->product_id, $group["ids"]->toArray())) {
                            $minQuantity = $minQuantity < $item->quantity ? $minQuantity : $item->quantity;
                            $discountPerc = $group['discount'];
                        }
                    }
                    foreach ($cart as $item) {
                        // check if product belongs to this group
                        if(in_array($item->product_id, $group["ids"]->toArray())) {
                            // calculate new discount
                            $newDiscount = $newDiscount + $this->getDiscount($item->price, $discountPerc, $minQuantity);
                        }
                    }
                    // add in used groups
                    $groupsUsed[] = $group['group_id'];
                }
            }
            // update discount info
            $data['discount'] = round($newDiscount, 2);
        }

        return response($data, 200); 
    }

    protected function getDiscount(int $price, int $percent, int $minAmount): float
    {
        return round(($price*$percent)/100 * $minAmount, 2);
    }
}

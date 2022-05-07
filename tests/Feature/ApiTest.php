<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Products;
use App\Models\Cart;
use App\Models\UserProductGroups;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if user can add some product in cart
     *
     * @return void
     */
    public function test_user_can_add_product_in_cart()
    {
        // create user and product
        $user = User::factory()->create();
        $product = Products::factory()->create();

        // login as new user and try add product in cart
        $response = $this->actingAs($user, 'sanctum')
        ->postJson('api/addProductInCart', [
            "product_id" => $product->id,
        ]);

        // check status and json
        $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) =>
            $json->hasAll(['message', 'status'])
        );
    }

    /**
     * Test if user can delete product from cart
     *
     * @return void
     */
    public function test_user_can_remove_product_from_cart()
    {
        $objs = $this->getUserWithCart();

        // login as new user and try remove product from cart
        $response = $this->actingAs($objs['user'], 'sanctum')
        ->postJson('api/removeProductFromCart', [
            "product_id" => $objs['product']->id,
        ]);

        // check status and json
        $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) =>
            $json->hasAll(['message', 'status'])
        );
    }

    /**
     * ensures that user can update quantity of item in cart
     *
     * @return void
     */
    public function test_user_can_set_product_quantity_in_cart()
    {
        $objs = $this->getUserWithCart();

        // login as new user and try to update product quantity in cart
        $response = $this->actingAs($objs['user'], 'sanctum')
        ->postJson('api/setCartProductQuantity', [
            "product_id" => $objs['product']->id,
            "quantity" => 2
        ]);

        // check status and json
        $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) =>
            $json->hasAll(['message', 'status'])
        );
    }

    public function test_it_can_return_correct_cart_info()
    {
        // create data
        $author = User::factory()->create();
        $product1 = Products::factory()->for($author)->create();
        $product2 = Products::factory()->for($author)->create();
        $group = UserProductGroups::factory()->for($author)
            ->hasAttached($product1)
            ->hasAttached($product2)
            ->create();

        // create user and add products in it's cart
        $user = User::factory()->create();
        $PC1 = Cart::factory()->for($user)->for($product1)->create();
        $PC2 = Cart::factory()->for($user)->for($product2)->create();

        // sent request to get cart object
        $response = $this->actingAs($user, 'sanctum')
        ->getJson('api/getUserCart');

        // get correct discount
        $discount = $this->getCorrectDiscount($PC1, $PC2, $group, $product1, $product2);

        // check status and json
        $response->assertStatus(200)
        ->assertJson(function (AssertableJson $json)  use ($discount) {
                $json->hasAll(['products', 'discount'])
                ->where("discount", $discount)->etc();
            }
        );

    }

    public function test_example_from_task_completed()
    {
        $this->seed();
        // get task example cart user
        $user = User::where("email", "cart@user.com")->first();
        $response = $this->actingAs($user, 'sanctum')
        ->getJson('api/getUserCart');

        // check status and json
        $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) =>
            $json->hasAll(['message', 'status'])
                ->where("discount", 10.5)
        );
    }

    protected function getUserWithCart(): array
    {
        $product = Products::factory()->for(User::factory())->create();
        $user = User::factory()->create();
        $cart = Cart::factory()->for($user)->for($product)->create();
        return [
            "user" => $user,
            "product" => $product
        ];
    }

    protected function getCorrectDiscount(
        Cart $pc1,
        Cart $pc2,
        UserProductGroups $group,
        Products $prod1,
        Products $prod2,
    ): float
    {
        // get to know, which one is lesser
        $min = $pc1->quantity < $pc2->quantity ? $pc1->quantity : $pc2->quantity;

        // count total of cart
        $total = ($prod1->price*$pc1->quantity) + ($prod2->price*$pc2->quantity);

        // dicount of percent
        $disPer = $group->dicount;

        // discount of first product
        $pr1dis = ($prod1->price*$disPer)/100 * $min;
        // discount of second product
        $pr2dis = ($prod2->price*$disPer)/100 * $min;

        return round($pr1dis + $pr2dis, 2);
    }
}

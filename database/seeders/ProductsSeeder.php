<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Products;
use App\Models\User;
use App\Models\Cart;
use App\Models\UserProductGroups;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds the database as example provided in task
     *
     * @return void
     */
    public function run()
    {
        // create user to be author of product group
        $userAuthor = User::factory([
            "name" => "Task example author",
            "email" => "author@user.com",
            "password" => bcrypt("12345678")
        ])->create();

        // create use to have products in cart
        $userCart = User::factory([
            "name" => "Task user for cart",
            "email" => "cart@user.com",
            "password" => bcrypt("12345678")
        ])->create();

        // create example products (from task)
        $product1 = $this->userCreatedProduct($userAuthor, "producti 1", 10);
        $product2 = $this->userCreatedProduct($userAuthor, "producti 2", 15);
        $product3 = $this->userCreatedProduct($userAuthor, "producti 3", 8);
        $product4 = $this->userCreatedProduct($userAuthor, "producti 4", 7);
        $product5 = $this->userCreatedProduct($userAuthor, "producti 5", 20);

        // create product group, attach products #2 and #5
        $productGroup = UserProductGroups::factory([
            "discount" => 15
        ])->for($userAuthor)->hasAttached($product2)->hasAttached($product5)->create();
// dd($product2);
        $this->addProductInCart($userCart, $product2, 3);
        $this->addProductInCart($userCart, $product5, 2);
        $this->addProductInCart($userCart, $product1, 1);
    }

    /**
     * Creates product related to specific user
     *
     * @param User $user
     * @param string $title
     * @param integer $price
     * @return Products
     */
    protected function userCreatedProduct(User $user, string $title, int $price): Products
    {
        return Products::factory([
            "title" => $title,
            "price" => $price,
        ])->for($user)->create();
    }

    /**
     * Adds product in cart for specific user, with specific quantity
     *
     * @param User $user
     * @param Products $product
     * @param integer $quantity
     * @return Cart
     */
    protected function addProductInCart(User $user, Products $product, int $quantity): Cart
    {
        return Cart::factory([
            "quantity" => $quantity
        ])->for($user)->for($product)->create();
    }
}

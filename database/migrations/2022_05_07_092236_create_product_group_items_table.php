<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * In the role of Pivot table between "UserProductGroups" and "Products" models
     * @return void
     */
    public function up()
    {
        Schema::create('product_group_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("group_id");
            $table->index('group_id');
            $table->foreign('group_id')->references('id')->on('user_product_groups')->onDelete('cascade');

            $table->unsignedBigInteger("product_id");
            $table->index('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_group_items');
    }
};

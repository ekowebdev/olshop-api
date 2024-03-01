<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->renameColumn('redeem_id', 'order_id');
            $table->renameColumn('item_gift_id', 'product_id');
            $table->renameColumn('redeem_quantity', 'quantity');
            $table->renameColumn('redeem_point', 'point');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->renameColumn('order_id', 'redeem_id');
            $table->renameColumn('product_id', 'item_gift_id');
            $table->renameColumn('quantity', 'redeem_quantity');
            $table->renameColumn('point', 'redeem_point');
        });
    }
};

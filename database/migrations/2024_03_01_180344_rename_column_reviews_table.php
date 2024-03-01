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
        Schema::table('reviews', function (Blueprint $table) {
            $table->renameColumn('redeem_id', 'order_id');
            $table->renameColumn('item_gift_id', 'product_id');
            $table->renameColumn('review_text', 'text');
            $table->renameColumn('review_rating', 'rating');
            $table->renameColumn('review_date', 'date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->renameColumn('order_id', 'redeem_id');
            $table->renameColumn('product_id', 'item_gift_id');
            $table->renameColumn('text', 'review_text');
            $table->renameColumn('rating', 'review_rating');
            $table->renameColumn('date', 'review_date');
        });
    }
};

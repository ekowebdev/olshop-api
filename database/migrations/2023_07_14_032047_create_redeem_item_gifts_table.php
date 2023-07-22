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
        Schema::create('redeem_item_gifts', function (Blueprint $table) {
            $table->unsignedBigInteger('redeem_id');
            $table->unsignedBigInteger('item_gift_id');
            $table->integer('redeem_quantity');
            $table->double('redeem_point', 10, 2);
            $table->timestamps();
            $table->foreign('redeem_id')->references('id')->on('redeems')->onDelete('cascade');
            $table->foreign('item_gift_id')->references('id')->on('item_gifts')->onDelete('cascade');
            $table->index(['redeem_id', 'item_gift_id'], 'index_redeem_item_gifts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redeem_item_gifts');
    }
};

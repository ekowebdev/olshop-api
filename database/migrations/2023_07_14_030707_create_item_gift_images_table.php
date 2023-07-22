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
        Schema::create('item_gift_images', function (Blueprint $table) {
            $table->unsignedBigInteger('item_gift_id');
            $table->string('item_gift_image_url', 255);
            $table->timestamps();
            $table->foreign('item_gift_id')->references('id')->on('item_gifts')->onDelete('cascade');
            $table->index(['item_gift_id'], 'index_item_gift_images');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_gift_images');
    }
};

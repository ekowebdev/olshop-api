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
        Schema::create('item_gifts', function (Blueprint $table) {
            $table->id();
            $table->string('item_gift_code', 15)->unique();
            $table->string('item_gift_name', 150);
            $table->text('item_gift_description');
            $table->double('item_gift_point', 10, 2);
            $table->integer('item_gift_quantity');
            $table->enum('item_gift_status', ['A', 'O'])->default('A')->comment('A = Available, O = Out of Stock');
            $table->timestamps();
            $table->index(['item_gift_code', 'item_gift_name'], 'index_item_gifts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_gifts');
    }
};

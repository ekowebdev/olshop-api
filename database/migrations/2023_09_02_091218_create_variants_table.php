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
        if (!Schema::hasTable('variants')) {
            Schema::create('variants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('item_gift_id');
                $table->string('variant_name', 150);
                $table->double('variant_point', 10, 2);
                $table->integer('variant_quantity');
                $table->timestamps();
                $table->foreign('item_gift_id')->references('id')->on('item_gifts')->onDelete('cascade');
                $table->index(['item_gift_id', 'variant_name'], 'index_variants');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variants');
    }
};

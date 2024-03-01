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
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('item_gift_code', 'code');
            $table->renameColumn('item_gift_name', 'name');
            $table->renameColumn('item_gift_slug', 'slug');
            $table->renameColumn('item_gift_description', 'description');
            $table->renameColumn('item_gift_spesification', 'spesification');
            $table->renameColumn('item_gift_point', 'point');
            $table->renameColumn('item_gift_weight', 'weight');
            $table->renameColumn('item_gift_quantity', 'quantity');
            $table->renameColumn('item_gift_status', 'status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('code', 'item_gift_code');
            $table->renameColumn('name', 'item_gift_name');
            $table->renameColumn('slug', 'item_gift_slug');
            $table->renameColumn('description', 'item_gift_description');
            $table->renameColumn('spesification', 'item_gift_spesification');
            $table->renameColumn('point', 'item_gift_point');
            $table->renameColumn('weight', 'item_gift_weight');
            $table->renameColumn('quantity', 'item_gift_quantity');
            $table->renameColumn('status', 'item_gift_status');
        });
    }
};

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
        Schema::table('variants', function (Blueprint $table) {
            $table->renameColumn('item_gift_id', 'product_id');
            $table->renameColumn('variant_name', 'name');
            $table->renameColumn('variant_slug', 'slug');
            $table->renameColumn('variant_point', 'point');
            $table->renameColumn('variant_weight', 'weight');
            $table->renameColumn('variant_quantity', 'quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->renameColumn('product_id', 'item_gift_id');
            $table->renameColumn('name', 'variant_name');
            $table->renameColumn('slug', 'variant_slug');
            $table->renameColumn('point', 'variant_point');
            $table->renameColumn('weight', 'variant_weight');
            $table->renameColumn('quantity', 'variant_quantity');
        });
    }
};

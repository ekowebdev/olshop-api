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
        Schema::table('item_gifts', function (Blueprint $table) {
            $table->double('item_gift_point', 10, 2)->nullable()->change();
            $table->integer('item_gift_quantity')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_gifts', function (Blueprint $table) {
            $table->double('item_gift_point', 10, 2)->nullable(false)->change();
            $table->integer('item_gift_quantity')->nullable(false)->change();
        });
    }
};

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
        Schema::table('item_gift_images', function (Blueprint $table) {
            if(Schema::hasColumn('item_gift_images', 'item_gift_image_url')){
                $table->renameColumn('item_gift_image_url', 'item_gift_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_gift_images', function (Blueprint $table) {
            //
        });
    }
};

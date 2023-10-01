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
            if (!Schema::hasColumn('item_gifts', 'item_gift_weight')) {
                $table->decimal('item_gift_weight', 10, 2)->nullable()->after('item_gift_point');
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
        Schema::table('item_gifts', function (Blueprint $table) {
            //
        });
    }
};

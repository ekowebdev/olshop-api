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
        Schema::table('shippings', function (Blueprint $table) {
            if (Schema::hasColumn('shippings', 'origin') && Schema::hasColumn('shippings', 'destination')) {
                $table->foreign('origin')->references('city_id')->on('cities')->onDelete('cascade');
                $table->foreign('destination')->references('city_id')->on('cities')->onDelete('cascade');
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
        Schema::table('shippings', function($table) {
            $table->dropForeign(['origin']);
            $table->dropForeign(['destination']);
        });
    }
};

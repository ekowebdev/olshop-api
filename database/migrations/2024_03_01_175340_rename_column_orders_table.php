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
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('redeem_code', 'code');
            $table->renameColumn('redeem_date', 'date');
            $table->renameColumn('redeem_status', 'status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('code', 'redeem_code');
            $table->renameColumn('date', 'redeem_date');
            $table->renameColumn('status', 'redeem_status');
        });
    }
};

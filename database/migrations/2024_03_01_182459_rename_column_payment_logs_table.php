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
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->renameColumn('payment_type', 'type');
            $table->renameColumn('redeem_id', 'order_id');
            $table->renameColumn('payment_status', 'status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->renameColumn('type', 'payment_type');
            $table->renameColumn('order_id', 'redeem_id');
            $table->renameColumn('status', 'payment_status');
        });
    }
};

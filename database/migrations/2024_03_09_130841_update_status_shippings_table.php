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
            \DB::statement("ALTER TABLE shippings MODIFY COLUMN status ENUM('on progress', 'on delivery', 'delivered', 'cancelled') DEFAULT 'on progress'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shippings', function (Blueprint $table) {
            \DB::statement("ALTER TABLE shippings MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending' NULL");
        });
    }
};

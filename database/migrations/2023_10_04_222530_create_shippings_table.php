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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('redeem_id');
            $table->integer('origin');
            $table->integer('destination');
            $table->decimal('weight', 9, 2);
            $table->string('courier');
            $table->string('service')->nullable();
            $table->string('description')->nullable();
            $table->double('cost', 10, 2);
            $table->string('etd')->nullable();
            $table->timestamps();
            $table->foreign('redeem_id')->references('id')->on('redeems')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shippings');
    }
};

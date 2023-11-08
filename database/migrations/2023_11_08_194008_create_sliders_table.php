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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('image', 200);
            $table->string('title', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('link', 100)->nullable();
            $table->integer('sort')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['A', 'N'])->default('A');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sliders');
    }
};

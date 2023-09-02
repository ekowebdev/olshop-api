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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 100)->unique();
            $table->string('category_name', 150)->unique();
            $table->string('category_slug')->unique();
            $table->integer('category_sort')->unique();
            $table->enum('category_status', ['A', 'X'])->default('A')->comment('A = Active, X = Non Active');
            $table->timestamps();
            $table->index(['category_code', 'category_slug', 'category_name'], 'index_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};

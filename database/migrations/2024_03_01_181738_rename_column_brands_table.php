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
        Schema::table('brands', function (Blueprint $table) {
            $table->renameColumn('brand_name', 'name');
            $table->renameColumn('brand_slug', 'slug');
            $table->renameColumn('brand_logo', 'logo');
            $table->renameColumn('brand_sort', 'sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->renameColumn('name', 'brand_name');
            $table->renameColumn('slug', 'brand_slug');
            $table->renameColumn('logo', 'brand_logo');
            $table->renameColumn('sort', 'brand_sort');
        });
    }
};

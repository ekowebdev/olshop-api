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
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('category_code', 'code');
            $table->renameColumn('category_name', 'name');
            $table->renameColumn('category_slug', 'slug');
            $table->renameColumn('category_image', 'image');
            $table->renameColumn('category_sort', 'sort');
            $table->renameColumn('category_status', 'status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('code', 'category_code');
            $table->renameColumn('name', 'category_name');
            $table->renameColumn('slug', 'category_slug');
            $table->renameColumn('image', 'category_image');
            $table->renameColumn('sort', 'category_sort');
            $table->renameColumn('status', 'category_status');
        });
    }
};

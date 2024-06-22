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
        Schema::table('subdistricts', function (Blueprint $table) {
            if (Schema::hasColumn('subdistricts', 'subdistrict_name')) {
                $table->renameColumn('subdistrict_name', 'name');
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
        Schema::table('subdistricts', function (Blueprint $table) {
            $table->renameColumn('name', 'subdistrict_name');
        });
    }
};

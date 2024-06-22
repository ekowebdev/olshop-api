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
        if (Schema::hasColumn('subdistricts', 'subdistrict_id')) {
            Schema::table('subdistricts', function (Blueprint $table) {
                $table->renameColumn('subdistrict_id', 'id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subdistricts', function (Blueprint $table) {
            $table->renameColumn('id', 'subdistrict_id');
        });
    }
};

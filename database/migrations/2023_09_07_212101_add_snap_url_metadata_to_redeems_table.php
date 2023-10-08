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
        Schema::table('redeems', function (Blueprint $table) {
            if (!Schema::hasColumn('redeems', 'snap_url') && !Schema::hasColumn('redeems', 'metadata')) {
                $table->string('snap_url')->nullable()->after('redeem_date');
                $table->json('metadata')->nullable()->after('snap_url');
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
        Schema::table('redeems', function (Blueprint $table) {
            $table->dropColumn('snap_url');
            $table->dropColumn('metadata');
        });
    }
};

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
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'subdistrict_id')) {
                $table->integer('subdistrict_id')->change();
            }
            if ($this->isFK('subdistricts', 'subdistrict_id')) {
                $table->dropForeign(['subdistrict_id']);
                $table->foreign('subdistrict_id')->references('subdistrict_id')->on('subdistricts')->onDelete('cascade');
            }
            $table->foreign('subdistrict_id')->references('subdistrict_id')->on('subdistricts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addresses', function($table) {
            $table->dropForeign(['subdistrict_id']);
            $table->dropColumn('subdistrict_id');
        });
    }

    private function isFK(string $table, string $column): bool
    {  
        $fkColumns = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys($table);

        return collect($fkColumns)->map(function ($fkColumn) {
            return $fkColumn->getColumns();
        })->flatten()->contains($column);
    }
};

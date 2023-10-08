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
            if (Schema::hasColumn('addresses', 'city_id') && Schema::hasColumn('addresses', 'province_id')) {
                $table->integer('province_id')->change();
                $table->integer('city_id')->change();
            }
            if (!$this->isFK('cities', 'city_id') && !$this->isFK('provinces', 'province_id')) {
                $table->foreign('city_id')->references('city_id')->on('cities')->onDelete('cascade');
                $table->foreign('province_id')->references('province_id')->on('provinces')->onDelete('cascade');
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
        Schema::table('addresses', function($table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['province_id']);
            $table->dropColumn('city_id');
            $table->dropColumn('province_id');
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

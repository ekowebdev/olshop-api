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
            // Check if columns exist in the 'addresses' table
            if (Schema::hasColumn('addresses', 'city_id') && Schema::hasColumn('addresses', 'province_id')) {
                $table->integer('province_id')->change();
                $table->integer('city_id')->change();
            }

            // Check if foreign keys exist and drop them if they do
            if ($this->isFK('addresses', 'city_id')) {
                $table->dropForeign(['city_id']);
            }
            if ($this->isFK('addresses', 'province_id')) {
                $table->dropForeign(['province_id']);
            }

            // Ensure the referenced columns exist in their respective tables
            if (Schema::hasColumn('cities', 'id')) {
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            }

            if (Schema::hasColumn('provinces', 'id')) {
                $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
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
        Schema::table('addresses', function (Blueprint $table) {
            if ($this->isFK('addresses', 'city_id')) {
                $table->dropForeign(['city_id']);
            }
            if ($this->isFK('addresses', 'province_id')) {
                $table->dropForeign(['province_id']);
            }
            $table->dropColumn('city_id');
            $table->dropColumn('province_id');
        });
    }

    /**
     * Check if a column is a foreign key.
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
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

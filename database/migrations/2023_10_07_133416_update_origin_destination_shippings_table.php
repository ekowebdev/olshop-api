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
        Schema::table('shippings', function (Blueprint $table) {
            if ($this->isFK('cities', 'id')) {
                $table->dropForeign(['origin']);
                $table->dropForeign(['destination']);
                $table->foreign('origin')->references('id')->on('cities')->onDelete('cascade');
                $table->foreign('destination')->references('id')->on('cities')->onDelete('cascade');
            }
            $table->foreign('origin')->references('id')->on('cities')->onDelete('cascade');
            $table->foreign('destination')->references('id')->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shippings', function($table) {
            $table->dropForeign(['origin']);
            $table->dropForeign(['destination']);
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

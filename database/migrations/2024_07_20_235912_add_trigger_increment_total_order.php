<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::unprepared('
            CREATE TRIGGER increment_total_order
            AFTER INSERT ON order_products
            FOR EACH ROW
            BEGIN
                UPDATE products
                SET total_order = total_order + NEW.quantity
                WHERE id = NEW.product_id;
            END;
        ');
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS increment_total_order');
    }
};

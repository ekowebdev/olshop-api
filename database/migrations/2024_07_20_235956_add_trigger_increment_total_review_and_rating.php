<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::unprepared('
            CREATE TRIGGER increment_total_review_and_total_rating
            AFTER INSERT ON reviews
            FOR EACH ROW
            BEGIN
                UPDATE products
                SET total_review = total_review + 1,
                    total_rating = (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE product_id = NEW.product_id)
                WHERE id = NEW.product_id;
            END;
        ');
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS increment_total_review_and_total_rating');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingOptionIdToProductVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variation', function (Blueprint $table) {
            $table->integer('shipping_option_id')->nullable()->before('created_at');
            $table->foreign('shipping_option_id')->references('id')
                ->on('shipping_options')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_variation', function (Blueprint $table) {
            $table->dropForeign(['shipping_option_id']); // Drop the foreign key first
            $table->dropColumn('shipping_option_id'); // Then drop the column
        });
    }

}

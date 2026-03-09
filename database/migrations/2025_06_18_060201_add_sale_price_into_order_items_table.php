<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

<<<<<<<< HEAD:database/migrations/2025_06_18_060201_add_sale_price_into_order_items_table.php
class AddSalePriceIntoOrderItemsTable extends Migration
========
class CreateProductAiTagsTable extends Migration
>>>>>>>> c5246da98 (ai tag in product store & update):database/migrations/2025_07_02_013637_create_product_ai_tags_table.php
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
<<<<<<<< HEAD:database/migrations/2025_06_18_060201_add_sale_price_into_order_items_table.php
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('sale_price')->after('sold_price')->nullable();
========
        Schema::create('product_ai_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('tag_title');
            $table->timestamps();
>>>>>>>> c5246da98 (ai tag in product store & update):database/migrations/2025_07_02_013637_create_product_ai_tags_table.php
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
<<<<<<<< HEAD:database/migrations/2025_06_18_060201_add_sale_price_into_order_items_table.php
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
========
        Schema::dropIfExists('product_ai_tags');
>>>>>>>> c5246da98 (ai tag in product store & update):database/migrations/2025_07_02_013637_create_product_ai_tags_table.php
    }
}

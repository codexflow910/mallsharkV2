<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

<<<<<<<< HEAD:database/migrations/2025_12_23_120547_add_bn_number_to_stores_table.php
class AddBnNumberToStoresTable extends Migration
========
class AddIsCustomColumnToProductsTable extends Migration
>>>>>>>> 9e51e84ee84a063d3923b1231da4815932ceafaa:database/migrations/2025_12_30_174817_add_is_custom_column_to_products_table.php
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
<<<<<<<< HEAD:database/migrations/2025_12_23_120547_add_bn_number_to_stores_table.php
        Schema::table('stores', function (Blueprint $table) {
            $table->string('BN_number')->nullable()->after('country');
========
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false);
>>>>>>>> 9e51e84ee84a063d3923b1231da4815932ceafaa:database/migrations/2025_12_30_174817_add_is_custom_column_to_products_table.php
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
<<<<<<<< HEAD:database/migrations/2025_12_23_120547_add_bn_number_to_stores_table.php
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('BN_number');
========
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_custom');
>>>>>>>> 9e51e84ee84a063d3923b1231da4815932ceafaa:database/migrations/2025_12_30_174817_add_is_custom_column_to_products_table.php
        });
    }
}

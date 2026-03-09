<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

<<<<<<<< HEAD:database/migrations/2025_12_23_125441_add_hs_code_to_products_table.php
class AddHsCodeToProductsTable extends Migration
========
class AddDeletedAtToStoresTable extends Migration
>>>>>>>> 53201d69ad6468e2936749026000774d08f5702d:database/migrations/2026_02_12_094805_add_deleted_at_to_stores_table.php
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
<<<<<<<< HEAD:database/migrations/2025_12_23_125441_add_hs_code_to_products_table.php
        Schema::table('products', function (Blueprint $table) {
            $table->string('HS_code')->nullable();
========
        Schema::table('stores', function (Blueprint $table) {
            $table->softDeletes();
>>>>>>>> 53201d69ad6468e2936749026000774d08f5702d:database/migrations/2026_02_12_094805_add_deleted_at_to_stores_table.php
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
<<<<<<<< HEAD:database/migrations/2025_12_23_125441_add_hs_code_to_products_table.php
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('HS_code');
========
        Schema::table('stores', function (Blueprint $table) {
            $table->dropSoftDeletes();
>>>>>>>> 53201d69ad6468e2936749026000774d08f5702d:database/migrations/2026_02_12_094805_add_deleted_at_to_stores_table.php
        });
    }
}

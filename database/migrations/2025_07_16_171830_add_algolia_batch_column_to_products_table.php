<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlgoliaBatchColumnToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('algolia_batch')->default(false)->after('eco_friendly')->comment('Indicates if the product is part of an Algolia batch operation');
            $table->index('algolia_batch', 'idx_algolia_batch'); // Adding an index for the algolia_batch column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_algolia_batch'); // Dropping the index
            $table->dropColumn('algolia_batch'); // Dropping the algolia_batch column
        });
    }
}

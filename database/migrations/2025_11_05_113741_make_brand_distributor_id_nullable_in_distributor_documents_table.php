<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeBrandDistributorIdNullableInDistributorDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('distributor_documents', function (Blueprint $table) {
            // Drop the existing foreign key first
            $table->dropForeign(['brand_distributor_id']);
        });

        Schema::table('distributor_documents', function (Blueprint $table) {
            // Modify the column to be nullable
            $table->unsignedBigInteger('brand_distributor_id')->nullable()->change();
        });

        Schema::table('distributor_documents', function (Blueprint $table) {
            // Re-add the foreign key
            $table->foreign('brand_distributor_id')
                  ->references('id')
                  ->on('brand_distributors')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('distributor_documents', function (Blueprint $table) {
            $table->dropForeign(['brand_distributor_id']);
        });

        Schema::table('distributor_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_distributor_id')->nullable(false)->change();
        });

        Schema::table('distributor_documents', function (Blueprint $table) {
            $table->foreign('brand_distributor_id')
                  ->references('id')
                  ->on('brand_distributors')
                  ->cascadeOnDelete();
        });
    }
}

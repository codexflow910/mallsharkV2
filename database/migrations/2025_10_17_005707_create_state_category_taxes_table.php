<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStateCategoryTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('state_category_taxes', function (Blueprint $table) {
            $table->id();
            $table->integer('state_id')->unsigned()->nullable();
            $table->foreign('state_id')->references('state_id')->on('states')->onDelete('set null');
            $table->bigInteger('tax_category_id')->unsigned()->nullable();
            $table->foreign('tax_category_id')->references('id')->on('tax_categories')->onDelete('set null');
            $table->bigInteger('mallshark_categories')->unsigned()->nullable();
            $table->foreign('mallshark_categories')->references('id')->on('categories')->onDelete('set null');
            $table->decimal('rate', 5, 4)->default(0);
            $table->timestamps();

            $table->unique(['state_id', 'mallshark_categories']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('state_category_taxes');
    }
}

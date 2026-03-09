<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPropertiesTable extends Migration
{
    public function up()
    {
        Schema::create('product_properties', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('filter_key');
            $table->string('value');
            $table->timestamps();

            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->index(['product_id', 'filter_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_properties');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryFilterValuesTable extends Migration
{
    public function up()
    {
        Schema::create('category_filter_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_filter_id');
            $table->string('value');
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('category_filter_id')->references('id')->on('category_filters')->onDelete('cascade');
            $table->index(['category_filter_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_filter_values');
    }
}

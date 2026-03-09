<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryFiltersTable extends Migration
{
    public function up()
    {
        Schema::create('category_filters', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->string('filter_key');
            $table->string('filter_name');
            $table->enum('filter_type', ['multi_select', 'single_select', 'boolean'])->default('multi_select');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->unique(['category_id', 'filter_key']);
            $table->index(['category_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_filters');
    }
}

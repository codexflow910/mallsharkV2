<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_visitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('product_id');
            $table->string('visitor_id');
            $table->date('visit_date');
            $table->timestamps();

            $table->unique(['store_id', 'product_id', 'visitor_id', 'visit_date'], 'product_visitors_unique');
            $table->index(['store_id', 'visit_date']);
            $table->index(['product_id', 'visit_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_visitors');
    }
}

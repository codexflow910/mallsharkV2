<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuperadminReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('superadmin_reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->unsignedInteger('product_id'); 
            $table->unsignedInteger('user_id'); 
            $table->integer('rating'); 
            $table->string('comment', 255)->nullable(); 
            $table->string('name'); 
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('superadmin_reviews');
    }
}

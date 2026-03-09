<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCommentColumnsInReviewsAndSuperadminReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->text('comment')->nullable()->change();
        });

        Schema::table('superadmin_reviews', function (Blueprint $table) {
            $table->text('comment')->nullable()->change();
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('comment', 400)->nullable()->change();
        });

        Schema::table('superadmin_reviews', function (Blueprint $table) {
            $table->string('comment', 255)->nullable()->change();
        });
    }
}

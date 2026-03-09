<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductMetadataToTikTokVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tik_tok_videos', function (Blueprint $table) {
            $table->string('product_title')->nullable();
            $table->decimal('product_price', 10, 2)->nullable();
            $table->string('product_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tik_tok_videos', function (Blueprint $table) {
            $table->dropColumn(['product_title','product_price','product_image'
            ]);
        });
    }
}

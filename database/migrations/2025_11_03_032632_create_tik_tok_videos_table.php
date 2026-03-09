<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTikTokVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tik_tok_videos', function (Blueprint $table) {
            $table->id();
            $table->string('video_id')->unique();
            $table->string('url');
            $table->string('title')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('product_link')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->text('embed_code')->nullable();
            
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('set null');
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
        Schema::dropIfExists('tik_tok_videos');
    }
}

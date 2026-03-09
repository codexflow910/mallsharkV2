<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAiFieldsToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('ai_enabled')->default(false);
            $table->integer('ai_tokens_used')->default(0);
            $table->integer('ai_tokens_remaining')->default(0);
            $table->timestamp('ai_quota_reset_at')->default(Carbon::now());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['ai_enabled', 'ai_tokens_used', 'ai_quota_reset_at']);
        });
    }
}

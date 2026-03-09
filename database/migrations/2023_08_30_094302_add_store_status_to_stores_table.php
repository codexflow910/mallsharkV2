<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoreStatusToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            DB::statement("ALTER TABLE stores MODIFY COLUMN store_status ENUM('0', '1', '2', '3') NOT NULL DEFAULT '1'");
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
            DB::statement("ALTER TABLE stores MODIFY COLUMN store_status ENUM('0', '1', '2') NOT NULL DEFAULT '0'");
        });
    }
}

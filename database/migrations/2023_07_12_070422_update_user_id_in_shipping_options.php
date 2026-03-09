<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserIdInShippingOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE shipping_options MODIFY user_id INT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE shipping_options MODIFY user_id INT NOT NULL");
    }
    
}

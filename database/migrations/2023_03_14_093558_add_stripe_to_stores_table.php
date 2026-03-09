<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->integer("stripe");
            $table->string('customer_id')->nullable();
            $table->string('bank_id')->nullable();
            $table->string('country')->nullable();
            $table->string('currency')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_holder_type')->nullable();
            $table->string('routing_number')->nullable();
            $table->string('account_number')->nullable();
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
            $table->dropColumn('stripe','customer_id', 'bank_id', 'country', 'currency', 'account_holder_name',
             'account_holder_type','routing_number', 'account_number' );
            
        });
    }
}

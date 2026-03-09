<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMallsharkPurchasedPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mallshark_purchased_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('package_name');
            $table->unsignedDecimal('amount');
            $table->enum('status', ['active', 'inactive', 'expired'])->default('inactive');
            $table->enum('charge_status', ['paid', 'unpaid',])->default('unpaid');
            $table->longText('session_id');
            $table->date('started_at');
            $table->date('expired_at');
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
        Schema::dropIfExists('mallshark_purchased_packages');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('excluded_product_id');
            $table->string('code_name')->unique();
            $table->string('description')->nullable();
            $table->enum('type', ['percent','fixprice','freesend']);
            $table->integer('value_discount')->nullable();
            $table->boolean('free_shipping')->default(false);
            $table->enum('requirements', ['none','monto','quantity']);
            $table->string('req_qty')->nullable();
            $table->integer('limit_number')->nullable();
            $table->boolean('limit_user')->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('times_used')->default(0);
            $table->float('discount')->default(0);
            $table->string('email_restrictions');
            $table->string('min_type')->nullable()->default(null);
            $table->integer('min_value')->default(0);
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
        Schema::dropIfExists('coupons');
    }
}

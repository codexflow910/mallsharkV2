<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarrierBasedShippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carrier_based_shippings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carrier_id');

            $table->enum('profile_type', ['general', 'custom']);
            $table->unsignedBigInteger('type_id');
            
            $table->boolean('additional_fee')->default(false);
            $table->enum('fee_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('fee_value', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('carrier_id')->references('id')->on('shipping_carriers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carrier_shippings');
    }
}

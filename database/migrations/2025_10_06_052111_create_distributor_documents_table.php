<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistributorDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_distributor_id')->constrained('brand_distributors')->cascadeOnDelete();
            $table->integer('distributor_id');  //store id
            $table->string('brand_name')->nullable();
            $table->string('brand_url')->nullable();
            $table->integer('distributor_logo')->nullable();
            $table->string('documents');
            $table->text('note')->nullable();
            $table->date('expiry_date');
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('distributors_documents');
    }
}

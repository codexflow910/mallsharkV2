<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrignalFileNameToPrductDigitalFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prduct_digital_files', function (Blueprint $table) {
            $table->string('orignal_file_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prduct_digital_files', function (Blueprint $table) {
            $table->string('orignal_file_name');
        });
    }
}

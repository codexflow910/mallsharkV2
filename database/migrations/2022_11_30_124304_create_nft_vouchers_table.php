<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNftVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nft_vouchers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('token_id')->unique();
            $table->json('price');
            $table->string('uri');
            $table->string('signature');
            $table->enum('is_minted',['0','1'])->comment('0 =>not minted , 1 => Minted');
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
        Schema::dropIfExists('nft_vouchers');
    }
}

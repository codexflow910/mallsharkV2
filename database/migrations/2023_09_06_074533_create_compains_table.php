<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Assuming 'slug' should be unique
            $table->text('description'); // Using 'text' for longer descriptions
            $table->integer('duration')->comment('days'); // Duration in days is fine as an integer
            $table->json('terms')->nullable(); // Allowing 'terms' and 'benefits' to be nullable
            $table->json('benefits')->nullable();
            $table->date('start_at');
            $table->date('end_at');
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
        Schema::dropIfExists('compains');
    }
}

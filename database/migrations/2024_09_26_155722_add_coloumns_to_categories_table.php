<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class AddColoumnsToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::table('categories', function (Blueprint $table) {
            // Add the new column
            $table->string('category_heading')->nullable()->after('title');
        });

        // Migrate data from the old column to the new column
        DB::table('categories')->update([
            'category_heading' => DB::raw('title')
        ]);

        // Drop the old column
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryToShippingProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            // Add nullable integer country column after city
            $table->unsignedBigInteger('country_id')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            $table->dropColumn('country_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomDiscountFieldsToDiscountApplicables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discount_applicables', function (Blueprint $table) {
            $table->enum('custom_discount_type', ['percent', 'fixed'])->nullable()->after('applicable_id');
            $table->decimal('custom_discount_value', 10, 2)->nullable()->after('custom_discount_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discount_applicables', function (Blueprint $table) {
            $table->dropColumn(['custom_discount_type', 'custom_discount_value']);
        });
    }
}

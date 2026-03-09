<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('state_category_taxes', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('rate');
            $table->string('postal_code', 20)->nullable()->after('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('state_category_taxes', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'postal_code']);
        });
    }
};

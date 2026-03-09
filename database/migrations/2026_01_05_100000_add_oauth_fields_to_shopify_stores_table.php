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
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->string('client_id')->nullable()->after('access_token');
            $table->string('client_secret')->nullable()->after('client_id');
            $table->enum('auth_type', ['access_token', 'oauth'])->default('access_token')->after('client_secret');
            $table->boolean('status')->default(false)->after('auth_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->dropColumn(['client_id', 'client_secret', 'auth_type']);
        });
    }
};

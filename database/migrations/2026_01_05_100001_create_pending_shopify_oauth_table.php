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
        Schema::create('pending_shopify_oauth', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('shop_url');
            $table->string('client_id');
            $table->text('client_secret'); // Encrypted
            $table->string('state', 64)->unique(); // CSRF token
            $table->json('permissions')->nullable();
            $table->timestamp('expires_at'); // OAuth states should expire
            $table->timestamps();

            $table->index('state');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_shopify_oauth');
    }
};

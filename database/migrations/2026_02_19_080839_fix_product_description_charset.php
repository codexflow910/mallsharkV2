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
        // Fix charset for description column to properly handle HTML special characters
        DB::statement('ALTER TABLE `products` MODIFY `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to latin1 if needed
        DB::statement('ALTER TABLE `products` MODIFY `description` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci');
    }
};

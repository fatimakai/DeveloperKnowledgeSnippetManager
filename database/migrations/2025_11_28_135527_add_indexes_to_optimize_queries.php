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
        Schema::table('snippets', function (Blueprint $table) {
            // Add indexes for common queries
            $table->index('title');  // For title search
            $table->index('is_public');  // For visibility filter
            $table->index('language');  // For language filter
            $table->index(['user_id', 'is_public']);  // For user's own + public snippets query
        });
        
        Schema::table('tags', function (Blueprint $table) {
            // Add index for tag name lookups
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('snippets', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['is_public']);
            $table->dropIndex(['language']);
            $table->dropIndex(['user_id', 'is_public']);
        });
        
        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};

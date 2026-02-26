<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update snippets with missing slugs using PHP to ensure compatibility across all databases
        $snippets = \DB::table('snippets')->whereNull('slug')->orWhere('slug', '')->get();
        
        foreach ($snippets as $snippet) {
            \DB::table('snippets')
                ->where('id', $snippet->id)
                ->update(['slug' => Str::slug($snippet->title) . '-' . uniqid()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prompt_analyses', function (Blueprint $table) {
            $table->longText('source_prompt_text')->nullable();
            $table->string('source_target_model', 100)->nullable();
            $table->timestamp('completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('prompt_analyses', function (Blueprint $table) {
            $table->dropColumn(['source_prompt_text', 'source_target_model', 'completed_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompt_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('prompt_text');
            $table->string('target_model', 100);
            $table->longText('example_input')->nullable();
            $table->longText('example_output')->nullable();
            $table->string('visibility', 20);
            $table->json('tags');
            $table->string('change_summary')->nullable();
            $table->timestamps();

            $table->unique(['prompt_id', 'version_number']);
            $table->index(['prompt_id', 'created_at']);
        });

        DB::table('prompts')->orderBy('id')->chunkById(100, function ($prompts): void {
            foreach ($prompts as $prompt) {
                $tags = DB::table('prompt_tag')
                    ->join('tags', 'tags.id', '=', 'prompt_tag.tag_id')
                    ->where('prompt_tag.prompt_id', $prompt->id)
                    ->orderBy('tags.name')
                    ->pluck('tags.name')
                    ->all();

                DB::table('prompt_versions')->insert([
                    'prompt_id' => $prompt->id,
                    'created_by' => $prompt->user_id,
                    'version_number' => 1,
                    'title' => $prompt->title,
                    'description' => $prompt->description,
                    'prompt_text' => $prompt->prompt_text,
                    'target_model' => $prompt->target_model,
                    'example_input' => $prompt->example_input,
                    'example_output' => $prompt->example_output,
                    'visibility' => $prompt->visibility,
                    'tags' => json_encode($tags, JSON_THROW_ON_ERROR),
                    'change_summary' => 'Initial version',
                    'created_at' => $prompt->created_at,
                    'updated_at' => $prompt->updated_at,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_versions');
    }
};

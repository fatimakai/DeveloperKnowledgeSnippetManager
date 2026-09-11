<?php

namespace App\Livewire;

use App\Models\Prompt;
use App\Models\Tag;
use App\Services\PromptVersionService;
use App\Support\PromptInput;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreatePrompt extends Component
{
    public string $title = '';

    public string $description = '';

    public string $promptText = '';

    public string $targetModel = '';

    public string $exampleInput = '';

    public string $exampleOutput = '';

    public string $visibility = Prompt::VISIBILITY_PRIVATE;

    public string $tags = '';

    protected function rules(): array
    {
        return PromptInput::livewireRules();
    }

    public function save()
    {
        $data = PromptInput::normalize($this->validate());

        DB::transaction(function () use ($data): void {
            $prompt = Prompt::create([
                'user_id' => auth()->id(),
                'title' => $data['title'],
                'description' => $data['description'] ?: null,
                'prompt_text' => $data['promptText'],
                'target_model' => $data['targetModel'],
                'example_input' => $data['exampleInput'] ?: null,
                'example_output' => $data['exampleOutput'] ?: null,
                'visibility' => $data['visibility'],
            ]);
            $prompt->tags()->sync($this->tagIds($data['tags']));
            app(PromptVersionService::class)->record($prompt, auth()->user());
        });

        session()->flash('success', 'Prompt created.');

        return $this->redirectRoute('prompts.mine');
    }

    public function render()
    {
        return view('livewire.prompt-form', ['heading' => 'Create prompt', 'submitLabel' => 'Create prompt']);
    }

    private function tagIds(string $tags)
    {
        return collect(explode(',', $tags))
            ->map(fn (string $tag) => trim(mb_strtolower($tag)))
            ->filter()
            ->unique()
            ->map(fn (string $tag) => Tag::firstOrCreate(['name' => $tag])->id);
    }
}

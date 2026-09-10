<?php

namespace App\Livewire;

use App\Models\Prompt;
use App\Models\Tag;
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'promptText' => ['required', 'string', 'max:50000'],
            'targetModel' => ['required', 'string', 'max:100'],
            'exampleInput' => ['nullable', 'string', 'max:10000'],
            'exampleOutput' => ['nullable', 'string', 'max:10000'],
            'visibility' => ['required', 'in:private,public'],
            'tags' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

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
            $prompt->tags()->sync($this->tagIds());
        });

        session()->flash('success', 'Prompt created.');

        return $this->redirectRoute('prompts.mine');
    }

    public function render()
    {
        return view('livewire.prompt-form', ['heading' => 'Create prompt', 'submitLabel' => 'Create prompt']);
    }

    private function tagIds()
    {
        return collect(explode(',', $this->tags))
            ->map(fn (string $tag) => trim(mb_strtolower($tag)))
            ->filter()
            ->unique()
            ->take(10)
            ->map(fn (string $tag) => Tag::firstOrCreate(['name' => mb_substr($tag, 0, 50)])->id);
    }
}

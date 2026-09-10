<?php

namespace App\Livewire;

use App\Models\Prompt;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class EditPrompt extends Component
{
    public Prompt $prompt;

    public string $title = '';

    public string $description = '';

    public string $promptText = '';

    public string $targetModel = '';

    public string $exampleInput = '';

    public string $exampleOutput = '';

    public string $visibility = Prompt::VISIBILITY_PRIVATE;

    public string $tags = '';

    public function mount(Prompt $prompt): void
    {
        Gate::authorize('update', $prompt);
        $prompt->load('tags');
        $this->prompt = $prompt;
        $this->title = $prompt->title;
        $this->description = $prompt->description ?? '';
        $this->promptText = $prompt->prompt_text;
        $this->targetModel = $prompt->target_model;
        $this->exampleInput = $prompt->example_input ?? '';
        $this->exampleOutput = $prompt->example_output ?? '';
        $this->visibility = $prompt->visibility;
        $this->tags = $prompt->tags->pluck('name')->implode(', ');
    }

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
        Gate::authorize('update', $this->prompt);
        $data = $this->validate();

        DB::transaction(function () use ($data): void {
            $this->prompt->update([
                'title' => $data['title'],
                'description' => $data['description'] ?: null,
                'prompt_text' => $data['promptText'],
                'target_model' => $data['targetModel'],
                'example_input' => $data['exampleInput'] ?: null,
                'example_output' => $data['exampleOutput'] ?: null,
                'visibility' => $data['visibility'],
            ]);
            $this->prompt->tags()->sync($this->tagIds());
        });

        session()->flash('success', 'Prompt updated.');

        return $this->redirectRoute('prompts.show', ['prompt' => $this->prompt]);
    }

    public function delete()
    {
        Gate::authorize('delete', $this->prompt);
        $this->prompt->delete();
        session()->flash('success', 'Prompt deleted.');

        return $this->redirectRoute('prompts.mine');
    }

    public function render()
    {
        return view('livewire.prompt-form', ['heading' => 'Edit prompt', 'submitLabel' => 'Save changes']);
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

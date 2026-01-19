<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("Welcome Back!") }}
                </div>
            </div>

            <!-- Snippets Leaderboard -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                        Leaderboard
                    </h3>
                    
                    <div class="grid gap-6">
                        @php
                            $snippets = \App\Models\Snippet::inRandomOrder()->limit(5)->get();
                        @endphp

                        @forelse($snippets as $index => $snippet)
                            <div class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition">
                                <!-- Rank Badge -->
<div class="flex-shrink-0">
    <div
        style="
            width: 25px;
            height: 25px;
            border-radius: 9999px;
            background-color: #6366f1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        "
    >
        {{ $index + 1 }}
    </div>
</div>







                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <!-- Title and Metadata -->
                                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                                        <a href="{{ route('snippets.edit', $snippet) }}" 
                                           class="text-lg font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                                            {{ $snippet->title }}
                                        </a>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ strtoupper($snippet->language) }}
                                        </span>
                                        @if($snippet->is_public)
                                            <span title="Public snippet" style="font-size: 1rem; color: #6366f1;">
                                                <i class="material-icons" style="font-size: 1rem; vertical-align: middle; color: #6366f1;">public</i>
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Description -->
                                    @if($snippet->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">
                                            {{ $snippet->description }}
                                        </p>
                                    @endif

                                    <!-- Tags -->
                                    @if($snippet->tags->count() > 0)
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            @foreach($snippet->tags->take(3) as $tag)
                                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    #{{ $tag->name }}
                                                </span>
                                            @endforeach
                                            @if($snippet->tags->count() > 3)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">+{{ $snippet->tags->count() - 3 }} more</span>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Author and Stats -->
                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                        <span>By {{ $snippet->user->name }}</span>
                                        <span>{{ count(explode("\n", $snippet->code)) }} lines</span>
                                        <span>{{ $snippet->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="flex-shrink-0">
                                    <a href="{{ route('snippets.edit', $snippet) }}"
                                       class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded shadow transition flex items-center gap-1">
                                        View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                                <p>No snippets available yet. Create your first one!</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- View All Button -->
                    <div class="mt-6 text-center">
                        <a href="{{ route('snippets.index') }}"
                           class="inline-block px-6 py-2 bg-indigo-500 hover:bg-indigo-600 text-white font-medium rounded-lg shadow transition">
                            View All Snippets
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

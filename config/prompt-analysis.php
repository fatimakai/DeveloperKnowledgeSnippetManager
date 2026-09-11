<?php

return [
    'provider' => 'openrouter',
    'model' => env('OPENROUTER_MODEL') ?: 'qwen/qwen3-next-80b-a3b-instruct',
    'per_hour' => (int) env('PROMPT_ANALYSIS_PER_HOUR', 5),
    'pro_per_hour' => (int) env('PROMPT_ANALYSIS_PRO_PER_HOUR', 25),
    'timeout' => (int) env('PROMPT_ANALYSIS_TIMEOUT', 60),
];

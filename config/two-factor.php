<?php

return [
    'window' => (int) env('TWO_FACTOR_WINDOW', 1),
    'challenge_ttl' => (int) env('TWO_FACTOR_CHALLENGE_TTL', 300),
    'max_attempts' => (int) env('TWO_FACTOR_MAX_ATTEMPTS', 5),
];

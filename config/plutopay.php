<?php

return [
    'test_mode' => env('PLUTOPAY_TEST_MODE', true),
    'webhook_timeout' => env('PLUTOPAY_WEBHOOK_TIMEOUT', 30),
    'webhook_max_retries' => env('PLUTOPAY_WEBHOOK_MAX_RETRIES', 5),
    'api_rate_limit' => env('PLUTOPAY_API_RATE_LIMIT', 100),
];

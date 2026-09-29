<?php

return [
    'base_url' => env('UNIFYUNITS_BASE_URL', 'https://api.unifyunits.com'),
    'api_key' => env('UNIFYUNITS_API_KEY'),
    'timeout' => (float) env('UNIFYUNITS_TIMEOUT', 10),
];

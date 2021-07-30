<?php

use Illuminate\Support\Str;

return [

    'default' => env('HTTP_LOGGER_WRITER', 'none'),

    'ignore' => [
        'methods' => ['options'],
        'paths' => [],
    ],

    'writers' => [
        'elasticsearch' => [
            'index' => Str::snake(env('APP_NAME') . '_http_logs'),
            'uses' => Zipzoft\HttpLogger\ElasticsearchWriter::class,
        ],
    ],
];
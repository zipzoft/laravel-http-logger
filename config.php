<?php

return [

    'default' => env('HTTP_LOGGER_WRITER', 'none'),

    'ignore' => [
        'methods' => ['options'],
        'paths' => [],
    ],

    'writers' => [
        'elasticsearch' => [
            //
        ],
    ],
];
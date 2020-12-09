<?php

return [
    'key'               => env('HWOBS_KEY', ''),
    'secret'            => env('HWOBS_SECRET', ''),
    'endpoint'          => env('HWOBS_ENDPOINT', ''),
    'bucket'            => env('HWOBS_BUCKET', ''),
    'security_token'    => false,
    'ssl_verify'        => false,
    'signature'         => 'v2',
    'path_style'        => false,
    'region'            => 'region',
    'ssl.certificate_authority' => '',
    'max_retry_count'           => 3,
    'timeout'                   => '',
    'socket_timeout'            => 60,
    'connect_timeout'           => 60,
    'chunk_size'                => 65536,
    'exception_response_mode'   => false,
    'log'                       => [
        'path'          => storage_path('logs'),
        'name'          => 'esdk-obs-php.log',
        'max_files'     => 30,
        'level'         => 300,
    ]
];
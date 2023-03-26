<?php

return [
    'endpoint' => [
        'localhost' => [
            'host' => env('SOLR_HOST', 'host.docker.internal'),
            'port' => env('SOLR_PORT', '8983'),
            'path' => env('SOLR_PATH', '/'),
            'core' => env('SOLR_CORE', 'gettingstarted')
        ]
    ]
];
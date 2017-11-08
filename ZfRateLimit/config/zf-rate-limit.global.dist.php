<?php
/**
 * zf-rate-limit configuration file
 *
 * @author Mike Alvarez <michaeljpalvarez@gmail.com>
 */

use ZfRateLimit\ZfRateLimitListener;
use ZfRateLimit\Storage\RedisStorage;

return [
    'zf_rate_limit' => [
        'listener' => ZfRateLimitListener::class,
        'storage' => [
            'name' => RedisStorage::class,
            'options' => [
                'host' => '127.0.0.1',
                'port' => 6379
            ]
        ],
        'controllers' => [
            'Test\\V1\\Rest\\Users\\Controller' => [
                'GET' => [
                    'limit' => 5,
                    'period' => 60
                ]
            ]
        ],
        'enabled' => true
    ]
];
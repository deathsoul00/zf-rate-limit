<?php
/**
 * module configuration list used by ZF library
 * here you can define your factories, application config, etc
 *
 * @author Mike Alvarez <michaeljpalvarez@gmail.com>
 */

use ZfRateLimit\ZfRateLimitListener;
use ZfRateLimit\ZfRateLimitListenerFactory;
use ZfRateLimit\Storage\RedisStorage;
use ZfRateLimit\Storage\RedisStorageFactory;

return [
    'service_manager' => [
        'factories' => [
            ZfRateLimitListener::class => ZfRateLimitListenerFactory::class,
            RedisStorage::class => RedisStorageFactory::class
        ]
    ]
];
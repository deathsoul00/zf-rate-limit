# zf-rate-limit
Zf Apigility module for implementing RateLimit to your API 

## How to use

Simple download and extract the `zip` file to your module folder and your API project and drop `zf-rate-limit.global.dist.php` in the config folder to `config/autoload` folder then remove the `.dist` to the filename. After that add `ZfRateLimit` to `modules.config.php` inside the `config` folder of your API.

In your `composer.json` add `"ZfRateLimit\\": "module/ZfRateLimit/src/"` to `autoload.ps4` and run `composer dump-autoload` to add `ZfRateLimit` module to your autoload classes

## Configuration

Here are the list of configuration inside the `zf-rate-limit.global.php`


- `listener` (string) - Handles the events `MvcEvent::EVENT_FINISH` and `MvcEvent::EVENT_ONROUTE` events. You can provide your own listener class for custom implementation of rate limit to your API `(Default: ZfRateLimit\ZfRateLimitListener)`
- `storage` (string|array) - storage used by the module. You can implement your own storage just by implementing the interface `ZfRateLimit\Storage\StorageInterface` (Default: array)
- `storage.name` (string) - name of the class storage to use
- `storage.options` (array) - options to be passed to your class
- `controllers` (array) - list of controllers that you want to have a rate limit implemented
- `controllers.{controller_name}` (array) - name of the controller
- `controllers.{controller_name}.{method}` (array) - sets the `limit` and `period` of the rate limit
- `controllers.{controller_name}.{method}.limit` (int) - number of requests/calls allowed
- `controllers.{controller_name}.{method}.period` (int) - time when to resets the limit in seconds
- `enable` (bool) - enable/disable the module

If you want to implement the rate limit to all of your `controllers` you can use the wildcard `*` and same goes for the `method` if you want to implement rate limit in all methods in your controller.

**Warning:** try to avoid using of `*` wildcard due to apigility documentation will also be affected when accessing using your api due to all controllers are being affected.

```php
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
            '{controller_name}' => [
                '{method}' => [
                    'limit' => 5,
                    'period' => 60
                ]
            ]
        ],
        'enabled' => true
    ]
];

// OR implements to all controller and all methods

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
            '*' => [
                '*' => [
                    'limit' => 5,
                    'period' => 60
                ]
            ]
        ],
        'enabled' => true
    ]
];

```

Feel free to customized this module.

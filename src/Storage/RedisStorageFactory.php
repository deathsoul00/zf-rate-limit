<?php
/**
 * factory class of RedisStorage
 *
 * @author Mike Alvarez <michaeljpalvarez@gmail.com>
 */
namespace ZfRateLimit\Storage;

use Interop\Container\ContainerInterface;

class RedisStorageFactory
{
    /**
     * invoke instance of RedisStorage
     *
     * @param  ContainerInterface $container
     *
     * @return \ZfRateLimit\Storage\RedisStorage
     */
    public function __invoke(ContainerInterface $container)
    {
        return new RedisStorage;
    }
}
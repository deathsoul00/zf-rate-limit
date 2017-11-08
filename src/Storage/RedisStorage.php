<?php
/**
 * storage handler using Redis as Storage
 *
 * @author Mike Alvarez <michaeljpalvarez@gmail.com>
 */
namespace ZfRateLimit\Storage;

use Redis;

class RedisStorage extends AbstractStorage
{
    /**
     * @var \Redis
     */
    protected $storage = null;

    /**
     * @inheritdoc
     */
    public function connect()
    {
        if (! isset($this->options['host'])) {
            throw new \UnexpectedValueException('host and port must be defined in the options');
        }

        // get connection parameter
        $host = $this->getOptions('host');
        $port = $this->getOptions('port');
        $timeout = $this->getOptions('timeout');
        $reserved = $this->getOptions('reserved');
        $retryInterval = $this->getOptions('retry_interval');

        $redis = new Redis;
        // connect to server
        $redis->connect($host, $port, $timeout, $reserved, $retryInterval);

        $this->storage = $redis;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $ttl = 1, $value = 1)
    {
        $this->storage->setEx($key, $ttl, $value);
    }

    /**
     * @inheritdoc
     */
    public function count($key)
    {
        return count($this->getKeys($key));
    }

    /**
     * get keys from the storage
     *
     * @param  string $key
     *
     * @return array
     */
    public function getKeys($key)
    {
        return $this->storage->keys($key . '*') ?: [];
    }
}
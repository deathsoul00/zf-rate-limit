<?php
/**
 * storage class interface
 * to create your own storage handler you must implement this interface
 *
 * @author Mike Alvarez <michaeljpalvarez@gmail.com>
 */

namespace ZfRateLimit\Storage;

interface StorageInterface
{
    /**
     * set defined options to storaage handler
     *
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * connect to persistent storage
     */
    public function connect();

    /**
     * set value to storage
     *
     * @param string   $key
     * @param integer  $ttl
     * @param mixed    $value
     */
    public function set($key, $ttl = 0, $value = NULL);

    /**
     * count values in the storage
     *
     * @param  string $key
     *
     * @return integer
     */
    public function count($key);

    /**
     * count values in the storage
     *
     * @param  string $key
     *
     * @return array
     */
    public function getKeys($key);
}
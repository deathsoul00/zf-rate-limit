<?php
/**
 * abstract class for storage handlers
 *
 * @author Mike Alvarez <michaeljpalvarez@gmail.com>
 */

namespace ZfRateLimit\Storage;

abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array $options [description]
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * get value from options array
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function getOptions($key, $default = NULL)
    {
        return (isset($this->options[$key])) ? $this->options[$key] : $default;
    }
}
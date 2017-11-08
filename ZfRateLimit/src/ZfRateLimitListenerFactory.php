<?php
/**
 * ZfRateLimitListener Factory Class
 *
 * @author Mike Alvarez <michaeljpalvarez@gmail.com>
 */

namespace ZfRateLimit;

use ZfRateLimit\Storage\StorageInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class ZfRateLimitListenerFactory
{
    /**
     * invoke ZfRateLimitListener class
     *
     * @param  ContainerInterface $container
     *
     * @return \ZfRateLimitListener
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = [];
        $config = $this->getConfig($container);

        $storage  = $this->getStorage($container, $config);
        $instance = new ZfRateLimitListener($storage);
        $instance->setConfig($config);

        return $instance;
    }

    /**
     * get defined storage from config
     *
     * @param  ContainerInterface $container
     * @param  array              $config
     *
     * @return \ZfRateLimit\Storage\StorageInterface
     */
    protected function getStorage(ContainerInterface $container, array $config)
    {
        if (isset($config['storage'])) {
            $storage = $config['storage'];
            if (is_array($storage)) {
                if ($container->has($storage['name'])) {
                    $options = $storage['options'];
                    $storage = $container->get($storage['name']);
                    $storage->setOptions($options);
                }
            } elseif ($container->has($config['storage'])) {
                $storage = $container->get($storage);
            }

            if (! $storage instanceof StorageInterface) {
                throw new ServiceNotCreatedException(
                    sprintf('storage defined must be an instanceof %s, %s given', StorageInterface::class, get_class($storage)),
                    500
                );
            }

            // call connect method
            $storage->connect();

            return $storage;
        }

        throw new ServiceNotCreatedException(
            sprintf(
                'unable to create service %s due to storage was not defined in the config',
                ZfRateLimitListener::class
            ),
            500
        );
    }

    /**
     * get needed config for the instance
     *
     * @param  ContainerInterface $container
     *
     * @return array
     */
    protected function getConfig(ContainerInterface $container)
    {
        $config = [];

        if ($container->has('config')) {
            $config = $container->get('config');
            if (isset($config['zf_rate_limit'])) {
                $config = $config['zf_rate_limit'];
            }
        }

        return $config;
    }
}
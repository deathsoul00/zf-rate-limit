<?php
/**
 * Module class for this module ZfRateLimit
 *
 * @author Mike Akvarez <michaeljpalvarez@gmail.com>
 */

namespace ZfRateLimit;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface, BootstrapListenerInterface
{
    /**
     * get module config
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * method to be called on application load/bootstrap
     *
     * @return void
     */
    public function onBootstrap(EventInterface $e)
    {
        $app = $e->getApplication();
        $em  = $app->getEventManager();
        $sm  = $app->getServiceManager();

        // default listener
        $listener = ZfRateLimitListener::class;

        // get defined listener
        if ($sm->has('config')) {
            $config = $sm->get('config');
            if (isset($config['zf_rate_limit'])) {
                $config = $config['zf_rate_limit'];
                $listener = (isset($config['listener'])) ? $config['listener'] : $listener;
            }
        }

        // attach event listener
        $sm->get(ZfRateLimitListener::class)->attach($em);
    }
}
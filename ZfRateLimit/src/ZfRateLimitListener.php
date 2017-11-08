<?php
/**
 * listener class for events in application
 *
 * @author Mike Alvarez <michaeljpalvarez@gmail.com>
 */

namespace ZfRateLimit;

use Zend\Http\Request as HttpRequest;
use Zend\Http\Headers;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Http\PhpEnvironment\RemoteAddress;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use ZfRateLimit\Storage\StorageInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class ZfRateLimitListener extends AbstractListenerAggregate
{
    /**
     * X-RateLimit-Limit Header Name
     *
     * @const
     */
    const X_RATELIMIT_LIMIT = 'X-RateLimit-Limit';

    /**
     * X-RateLimit-Remaining Header Name
     *
     * @const
     */
    const X_RATELIMIT_LIMIT_REMAINING = 'X-RateLimit-Remaining';

    /**
     * X-RateLimit-Reset Header Name
     *
     * @const
     */
    const X_RATELIMIT_LIMIT_RESET = 'X-RateLimit-Reset';

    /**
     * rate limit storage
     *
     * @var \ZfRateLimit\Storage\StorageInterface
     */
    protected $storage = null;

    /**
     * rate limit config
     *
     * @var array
     */
    protected $config = [];

    /**
     * current method config
     *
     * @var array
     */
    protected $methodConfig = [];

    /**
     * ZfRateLimitListener constructor
     *
     * @param ZfRateLimit\Storage\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * attach event listeners
     *
     * @param  EventManagerInterface $event
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], -1000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, [$this, 'onResponse'], -1000);
    }

    /**
     * set rate limit configuration
     *
     * @param  array $config
     *
     * @return self
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * method to be called on event MvcEvent::EVENT_ROUTE
     *
     * @param  MvcEvent $e
     */
    public function onRoute(MvcEvent $e)
    {
        // check if module is enabled
        if (empty($this->config['enabled'])) {
            return;
        }

        /* @var $request HttpRequest */
        $request = $e->getRequest();
        if (! $request instanceof HttpRequest) {
            return;
        }

        // get controllers defined
        $controllers = [];
        if (! isset($this->config['controllers'])) {
            return;
        } else {
            $controllers = $this->config['controllers'];
        }

        $routeMatch  = $e->getRouteMatch();
        $action      = $routeMatch->getParam('action');
        $controller  = $routeMatch->getParam('controller');
        $routeName   = $routeMatch->getMatchedRouteName();

        $controllerConfig = [];

        /**
         * checks if current request controller is defined in order
         *
         * checks $controller
         * checks $controller::$action
         * checks $routeName
         * checks '*' (wildcard for all)
         * if nothing found do nothing
         */
        if (! empty($controllers[$controller])) {
            $controllerConfig = $controllers[$controller];
        } elseif (! empty($controllers["$controller::$action"])) {
            $controllerConfig = $controllers["$controller::$action"];
        } elseif (! empty($controllers[$routeName])) {
            $controllerConfig = $controllers[$routeName];
        } elseif (! empty($controllers['*'])) {
            $controllerConfig = $controllers['*'];
        } else {
            return;
        }

        $method = strtoupper($request->getMethod());

        /**
         * checks for current method config in order
         *
         * checks current method
         * checks '*' (wildcard for all)
         */
        if (! empty($controllerConfig[$method])) {
            $this->methodConfig = $controllerConfig[$method];
        } elseif (! empty($controllerConfig['*'])) {
            $this->methodConfig = $controllerConfig[$method];
        } else {
            return;
        }

        // checks limit rate
        return $this->checkRateLimit();
    }

    /**
     * method to be called on event MvcEvent::EVENT_FINISH
     *
     * @param  MvcEvent $e
     */
    public function onResponse(MvcEvent $e)
    {
        // check if module is enabled
        if (empty($this->config['enabled'])) {
            return;
        }

        /* @var $response HttpResponse */
        $response = $e->getResponse();
        if (! $response instanceof HttpResponse) {
            return;
        }

        /* @var $headers Headers */
        $headers = $response->getHeaders();

        // set headers
        $this->setXRateLimit($headers)
            ->setXRateLimitRemaining($headers)
            ->setXRateLimitResets($headers);
    }

    /**
     * @param  Headers $headers
     *
     * @return self
     */
    protected function setXRateLimit(Headers $headers)
    {
        $headers->addHeaderLine(self::X_RATELIMIT_LIMIT, $this->getLimit());

        return $this;
    }

    /**
     * @param  Headers $headers
     *
     * @return self
     */
    protected function setXRateLimitRemaining(Headers $headers)
    {
        $headers->addHeaderLine(self::X_RATELIMIT_LIMIT_REMAINING, $this->getRemainingCalls());

        return $this;
    }

    /**
     * @param  Headers $headers
     *
     * @return self
     */
    protected function setXRateLimitResets(Headers $headers)
    {
        $time = $this->getTimeToReset();

        $date = new \DateTime;
        $date->setTimestamp($time);
        $date = $date->format('Y-m-d H:i:s');

        $headers->addHeaderLine(self::X_RATELIMIT_LIMIT_RESET, $date);

        return $this;
    }

    /**
     * checks current request rate limit available
     */
    protected function checkRateLimit()
    {
        // if no more remaing calls
        if ($this->getRemainingCalls() <= 0) {
            // throw error
            return new ApiProblemResponse(new ApiProblem(429, 'Too many requests please try again later'));
        }

        // create record for the current ip
        $this->storage->set($this->getKey(), $this->getPeriod(), 1);
    }

    /**
     * @return int
     */
    protected function getRemainingCalls()
    {
        $limit = $this->getLimit();
        $calls = $this->storage->count($this->getUserIp());

        $this->getTimeToReset();

        return ($limit - $calls);
    }

    /**
     * @return int
     */
    protected function getPeriod()
    {
        return (isset($this->methodConfig['period'])) ? $this->methodConfig['period'] : 0;
    }

    /**
     * @return int
     */
    protected function getLimit()
    {
        return (isset($this->methodConfig['limit'])) ? $this->methodConfig['limit'] : 0;
    }

    /**
     * @return string
     */
    protected function getKey()
    {
        return $this->getUserIp() . '-' . time();
    }

    /**
     * @return int
     */
    protected function getTimeToReset()
    {
        $keys = $this->storage->getKeys($this->getUserIp());

        // get unix time defined in the keys after '-'
        $time = array_map(function($key) {
            return str_replace($this->getUserIp() . '-', NULL, $key);
        }, $keys);

        if ($time) {
            $time = max($time);
        } else {
            $time = 0;
        }

        return $time + $this->getPeriod();
    }

    /**
     * get current client ip address
     *
     * @return string
     */
    protected function getUserIp()
    {
        $env = new RemoteAddress;
        return $env->getIpAddress();
    }
}
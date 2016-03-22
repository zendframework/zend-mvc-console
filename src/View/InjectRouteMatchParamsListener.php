<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Console\View;

use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\Parameters;

class InjectRouteMatchParamsListener extends AbstractListenerAggregate
{
    /**
     * Should request params overwrite existing request params?
     *
     * @var bool
     */
    protected $overwrite = true;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 91);
    }

    /**
     * Take parameters from RouteMatch and inject them into the request.
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function onDispatch(MvcEvent $e)
    {
        $request = $e->getRequest();
        if (! $request instanceof ConsoleRequest) {
            // unsupported request type
            return;
        }

        $routeMatchParams = $e->getRouteMatch()->getParams();
        $params = $request->params();

        if ($this->overwrite) {
            $this->overwriteParams($routeMatchParams, $params);
            return;
        }

        $this->addParams($routeMatchParams, $params);
    }

    /**
     * Should RouteMatch parameters replace existing Request params?
     *
     * @param  bool $overwrite
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = $overwrite;
    }

    /**
     * @return bool
     */
    public function getOverwrite()
    {
        return $this->overwrite;
    }

    /**
     * Overwrite parameters from the route match to the request
     *
     * @param array $routeMatchParams
     * @param Parameters $params
     * return void
     */
    private function overwriteParams(array $routeMatchParams, Parameters $params)
    {
        foreach ($routeMatchParams as $key => $val) {
            $params->$key = $val;
        }
    }

    /**
     * Add (but do not overwrite) parameters from the route match to the request.
     *
     * @param array $routeMatchParams
     * @param Parameters $params
     * return void
     */
    private function addParams(array $routeMatchParams, Parameters $params)
    {
        foreach ($routeMatchParams as $key => $val) {
            if (!$params->offsetExists($key)) {
                $params->$key = $val;
            }
        }
    }
}

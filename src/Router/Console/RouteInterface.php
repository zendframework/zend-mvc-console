<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright  Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Mvc\Router\Console;

use Zend\Mvc\Router\RouteInterface as BaseRoute;

interface RouteInterface extends BaseRoute
{
    /**
     * Get a list of parameters used while assembling.
     *
     * @return array
     */
    public function getAssembledParams();
}

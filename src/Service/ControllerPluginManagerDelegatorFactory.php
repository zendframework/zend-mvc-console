<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Console\Controller\Plugin\CreateConsoleNotFoundModel;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class ControllerPluginManagerDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Add console-specific plugins to the controller PluginManager.
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param null|array $options
     * @return \Zend\Mvc\Controller\PluginManager
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $plugins = $callback();

        $plugins->setAlias('CreateConsoleNotFoundModel', CreateConsoleNotFoundModel::class);
        $plugins->setAlias('createConsoleNotFoundModel', CreateConsoleNotFoundModel::class);
        $plugins->setAlias('createconsolenotfoundmodel', CreateConsoleNotFoundModel::class);
        $plugins->setFactory(CreateConsoleNotFoundModel::class, InvokableFactory::class);

        return $plugins;
    }

    /**
     * Add console-specific plugins to the controller PluginManager. (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param string $name
     * @param string $requestedName
     * @param callable $callback
     * @return \Zend\Mvc\Controller\PluginManager
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }
}

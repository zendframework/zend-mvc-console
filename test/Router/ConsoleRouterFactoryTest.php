<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Router;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Router\ConsoleRouterFactory;
use Zend\Mvc\Console\Router\SimpleRouteStack;
use Zend\Router\RoutePluginManager;

class ConsoleRouterFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new ConsoleRouterFactory();
    }

    public function testReturnsASimpleRouteStackByDefaultWithNoConfig()
    {
        $container = $this->container;
        $container->has('config')->willReturn(false);
        $container->get('RoutePluginManager')->will(function () use ($container) {
            return new RoutePluginManager($container->reveal());
        });
        $router = $this->factory->__invoke($container->reveal(), 'ConsoleRouter');
        $this->assertInstanceOf(SimpleRouteStack::class, $router);
        $this->assertCount(0, $router->getRoutes());
    }

    public function testWillUseEmptyConfigToCreateSimpleRouteStackIfPresent()
    {
        $container = $this->container;
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([]);
        $container->get('RoutePluginManager')->will(function () use ($container) {
            return new RoutePluginManager($container->reveal());
        });
        $router = $this->factory->__invoke($container->reveal(), 'ConsoleRouter');
        $this->assertInstanceOf(SimpleRouteStack::class, $router);
        $this->assertCount(0, $router->getRoutes());
    }

    public function testWillUseEmptyRouterConfigToCreateSimpleRouteStackIfPresent()
    {
        $container = $this->container;
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['console' => ['router' => []]]);
        $container->get('RoutePluginManager')->will(function () use ($container) {
            return new RoutePluginManager($container->reveal());
        });
        $router = $this->factory->__invoke($container->reveal(), 'ConsoleRouter');
        $this->assertInstanceOf(SimpleRouteStack::class, $router);
        $this->assertCount(0, $router->getRoutes());
    }

    public function testWillUseRouterConfigToCreateSimpleRouteStack()
    {
        $container = $this->container;
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['console' => ['router' => ['routes' => [
            'test' => [
                'options' => [
                    'route' => 'test',
                ],
            ],
        ]]]]);
        $container->get('RoutePluginManager')->will(function () use ($container) {
            return new RoutePluginManager($container->reveal());
        });
        $router = $this->factory->__invoke($container->reveal(), 'ConsoleRouter');
        $this->assertInstanceOf(SimpleRouteStack::class, $router);
        $this->assertCount(1, $router->getRoutes());
    }
}

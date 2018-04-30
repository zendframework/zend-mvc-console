<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionMethod;
use Zend\Mvc\Application;
use Zend\Mvc\Console\Service\ConsoleViewHelperManagerDelegatorFactory;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;
use Zend\Router\RouteStackInterface;
use Zend\View\Helper;
use Zend\View\HelperPluginManager;

class ConsoleViewHelperManagerDelegatorFactoryTest extends TestCase
{
    use FactoryEnvironmentTrait;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class)->reveal();
        $this->plugins   = $this->prophesize(HelperPluginManager::class);
        $this->callback  = function () {
            return $this->plugins->reveal();
        };
        $this->factory   = new ConsoleViewHelperManagerDelegatorFactory();
    }

    public function testReturnsPluginsUnalteredWhenNotInConsoleEnvironment()
    {
        $this->setConsoleEnvironment(false);

        $this->plugins->setFactory(Helper\Url::class, Argument::type('callable'))->shouldNotBeCalled();
        $this->plugins->setFactory('zendviewhelperurl', Argument::type('callable'))->shouldNotBeCalled();
        $this->plugins->setFactory(Helper\BasePath::class, Argument::type('callable'))->shouldNotBeCalled();
        $this->plugins->setFactory('zendviewhelperbasepath', Argument::type('callable'))->shouldNotBeCalled();

        $this->assertSame(
            $this->plugins->reveal(),
            $this->factory->__invoke($this->container, 'ViewHelperManager', $this->callback)
        );
    }

    public function testInjectsPluginFactoriesWhenInConsoleEnvironment()
    {
        $this->setConsoleEnvironment(true);

        $this->plugins->setFactory(Helper\Url::class, Argument::type('callable'))->shouldBeCalled();
        $this->plugins->setFactory('zendviewhelperurl', Argument::type('callable'))->shouldBeCalled();
        $this->plugins->setFactory(Helper\BasePath::class, Argument::type('callable'))->shouldBeCalled();
        $this->plugins->setFactory('zendviewhelperbasepath', Argument::type('callable'))->shouldBeCalled();

        $this->assertSame(
            $this->plugins->reveal(),
            $this->factory->__invoke($this->container, 'ViewHelperManager', $this->callback)
        );
    }

    public function testCreateUrlHelperFactoryInjectsHelperWithRouterAndRouteMatchWhenPresent()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $router = $this->prophesize(RouteStackInterface::class);
        $container->get('HttpRouter')->will([$router, 'reveal']);

        $routeMatch = $this->prophesize(RouteMatch::class);

        $mvcEvent = $this->prophesize(MvcEvent::class);
        $mvcEvent->getRouteMatch()->will([$routeMatch, 'reveal']);

        $application = $this->prophesize(Application::class);
        $application->getMvcEvent()->will([$mvcEvent, 'reveal']);
        $container->get('Application')->will([$application, 'reveal']);

        $r = new ReflectionMethod($this->factory, 'createUrlHelperFactory');
        $r->setAccessible(true);
        $factory = $r->invoke($this->factory, $container->reveal());

        $helper = $factory();

        $this->assertAttributeSame($router->reveal(), 'router', $helper);
        $this->assertAttributeSame($routeMatch->reveal(), 'routeMatch', $helper);
    }
}

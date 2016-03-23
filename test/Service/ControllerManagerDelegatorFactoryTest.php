<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Mvc\Console\Service\ControllerManagerDelegatorFactory;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

class ControllerManagerDelegatorFactoryTest extends TestCase
{
    public function testInjectsConsoleInitializerIntoControllerManager()
    {
        $controllers = $this->prophesize(ControllerManager::class);
        $controllers->addInitializer(Argument::that(function ($argument) {
            if (! is_callable($argument)) {
                return false;
            }

            if (! is_array($argument) || 2 !== count($argument)) {
                return false;
            }

            $object = array_shift($argument);
            if (! $object instanceof ControllerManagerDelegatorFactory) {
                return false;
            }

            $method = array_shift($argument);
            if ($method !== 'injectConsole') {
                return false;
            }

            return true;
        }))->shouldBeCalled();

        $callback = function () use ($controllers) {
            return $controllers->reveal();
        };

        $factory = new ControllerManagerDelegatorFactory();

        $this->assertSame($controllers->reveal(), $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
            'ControllerManager',
            $callback
        ));
    }

    public function testInitializerDoesNothingForNonAbstractConsoleControllers()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('Console')->shouldNotBeCalled();
        $instance = (object) [];

        $factory = new ControllerManagerDelegatorFactory();
        $this->assertNull($factory->injectConsole($container->reveal(), $instance));
    }

    public function testInitializerInjectsConsoleIntoAbstractConsoleControllers()
    {
        $console = $this->prophesize(AdapterInterface::class)->reveal();

        $controller = $this->prophesize(AbstractConsoleController::class);
        $controller->setConsole($console)->shouldBeCalled();

        // Using SM instance to allow testing against both v2 and v3
        $container = new ServiceManager();
        $container->setService('Console', $console);

        $factory = new ControllerManagerDelegatorFactory();
        $this->assertNull($factory->injectConsole($container, $controller->reveal()));
    }

    public function testFlippedArgumentInitializerDoesNothingForNonAbstractConsoleControllers()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('Console')->shouldNotBeCalled();
        $instance = (object) [];

        $factory = new ControllerManagerDelegatorFactory();
        $this->assertNull($factory->injectConsole($instance, $container->reveal()));
    }

    public function testFlippedArgumentInitializerInjectsConsoleIntoAbstractConsoleControllers()
    {
        $console = $this->prophesize(AdapterInterface::class)->reveal();

        $controller = $this->prophesize(AbstractConsoleController::class);
        $controller->setConsole($console)->shouldBeCalled();

        // Using SM instance to allow testing against both v2 and v3
        $container = new ServiceManager();
        $container->setService('Console', $console);

        $factory = new ControllerManagerDelegatorFactory();
        $this->assertNull($factory->injectConsole($controller->reveal(), $container));
    }
}

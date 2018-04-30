<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Router;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Router\ConsoleRouterDelegatorFactory;
use ZendTest\Mvc\Console\Service\FactoryEnvironmentTrait;

class ConsoleRouterDelegatorFactoryTest extends TestCase
{
    use FactoryEnvironmentTrait;

    public function environments()
    {
        return [
            'console' => [true],
            'http'    => [false],
        ];
    }

    /**
     * @dataProvider environments
     */
    public function testReturnsOriginalServiceWhenNotConsoleEnvironment($consoleFlag)
    {
        $this->setConsoleEnvironment($consoleFlag);
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('ConsoleRouter')->shouldNotBeCalled();
        $factory   = new ConsoleRouterDelegatorFactory();

        $this->assertEquals('FOO', $factory(
            $container->reveal(),
            'not-a-router',
            function () {
                return 'FOO';
            }
        ));
    }

    /**
     * @dataProvider environments
     */
    public function testReturnsConsoleRouterServiceIfRequestedNameIsConsoleRouter($consoleFlag)
    {
        $this->setConsoleEnvironment($consoleFlag);
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('ConsoleRouter')->willReturn('ConsoleRouter');

        $factory   = new ConsoleRouterDelegatorFactory();

        $this->assertEquals('ConsoleRouter', $factory(
            $container->reveal(),
            'ConsoleRouter',
            function () {
                return 'FOO';
            }
        ));
    }

    public function routerServiceNames()
    {
        return [
            ['router'],
            ['Router'],
            ['ROUTER'],
        ];
    }

    /**
     * @dataProvider routerServiceNames
     */
    public function testReturnsConsoleRouterServiceIfRequestedNameIsRouterAndInConsoleEnvironment($routerServiceName)
    {
        $this->setConsoleEnvironment(true);
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('ConsoleRouter')->willReturn('ConsoleRouter');

        $factory   = new ConsoleRouterDelegatorFactory();

        $this->assertEquals('ConsoleRouter', $factory(
            $container->reveal(),
            $routerServiceName,
            function () {
                return 'FOO';
            }
        ));
    }

    /**
     * @dataProvider routerServiceNames
     */
    public function testReturnsOriginalServiceIfRequestedRoutingInterfaceAndNotInConsoleEnvironment($routerServiceName)
    {
        $this->setConsoleEnvironment(false);
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('ConsoleRouter')->shouldNotBeCalled();

        $factory   = new ConsoleRouterDelegatorFactory();

        $this->assertEquals('FOO', $factory(
            $container->reveal(),
            $routerServiceName,
            function () {
                return 'FOO';
            }
        ));
    }
}

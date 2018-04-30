<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Service\ViewManagerDelegatorFactory;

class ViewManagerDelegatorFactoryTest extends TestCase
{
    use FactoryEnvironmentTrait;

    public function setUp()
    {
        $this->factory = new ViewManagerDelegatorFactory();
    }

    public function testReturnsReturnValueOfCallbackWhenNotInConsoleEnvironment()
    {
        $this->setConsoleEnvironment(false);

        $callback = function () {
            return 'FOO';
        };

        $this->assertSame(
            $callback(),
            $this->factory->__invoke($this->createContainer(), 'ViewManager', $callback)
        );
    }

    public function testReturnsConsoleViewManagerWhenInConsoleEnvironment()
    {
        $this->setConsoleEnvironment(true);

        $viewManager = (object) ['view' => true];
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('ConsoleViewManager')->willReturn(true);
        $container->get('ConsoleViewManager')->willReturn($viewManager);

        $callback = function () {
            return 'FOO';
        };

        $result = $this->factory->__invoke($container->reveal(), 'ViewManager', $callback);
        $this->assertSame($viewManager, $result);
    }
}

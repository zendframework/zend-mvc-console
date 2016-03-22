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
use Zend\Mvc\Console\Service\ConsoleViewHelperManagerDelegatorFactory;
use Zend\View\HelperPluginManager;
use Zend\View\Helper;

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
}

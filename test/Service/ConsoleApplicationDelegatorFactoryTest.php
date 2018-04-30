<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\ApplicationInterface;
use Zend\Mvc\Console\Service\ConsoleApplicationDelegatorFactory;

class ConsoleApplicationDelegatorFactoryTest extends TestCase
{
    use FactoryEnvironmentTrait;

    public function setUp()
    {
        $this->factory = new ConsoleApplicationDelegatorFactory();
    }

    public function testFactoryReturnsApplicationUntouchedWhenNotInConsoleEnvironment()
    {
        $this->setConsoleEnvironment(false);

        $application = $this->prophesize(ApplicationInterface::class);
        $application->getEventManager()->shouldNotBeCalled();

        $callback = function () use ($application) {
            return $application->reveal();
        };

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('ConsoleViewManager')->shouldNotBeCalled();

        $this->assertSame(
            $application->reveal(),
            $this->factory->__invoke($container->reveal(), 'Application', $callback)
        );
    }

    public function testFactoryPassesApplicationEventManagerToConsoleViewManager()
    {
        $this->setConsoleEnvironment(true);

        $events = $this->prophesize(EventManagerInterface::class)->reveal();

        $application = $this->prophesize(ApplicationInterface::class);
        $application->getEventManager()->willReturn($events);

        $callback = function () use ($application) {
            return $application->reveal();
        };

        $aggregate = $this->prophesize(ListenerAggregateInterface::class);
        $aggregate->attach($events)->shouldBeCalled();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('ConsoleViewManager')->willReturn($aggregate->reveal());

        $this->assertSame(
            $application->reveal(),
            $this->factory->__invoke($container->reveal(), 'Application', $callback)
        );
    }
}

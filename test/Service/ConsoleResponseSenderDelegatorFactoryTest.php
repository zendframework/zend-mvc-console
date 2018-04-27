<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventsCapableInterface;
use Zend\Mvc\Console\ResponseSender\ConsoleResponseSender;
use Zend\Mvc\Console\Service\ConsoleResponseSenderDelegatorFactory;
use Zend\Mvc\ResponseSender\SendResponseEvent;

class ConsoleResponseSenderDelegatorFactoryTest extends TestCase
{
    public function testAttachesConsoleResponseSenderToSendResponseListener()
    {
        $events = $this->prophesize(EventManagerInterface::class);
        $events->attach(
            SendResponseEvent::EVENT_SEND_RESPONSE,
            Argument::type(ConsoleResponseSender::class),
            -2000
        )->shouldBeCalled();

        $listener = $this->prophesize(EventsCapableInterface::class);
        $listener->getEventManager()->willReturn($events->reveal());

        $container = $this->prophesize(ContainerInterface::class)->reveal();

        $callback = function () use ($listener) {
            return $listener->reveal();
        };

        $factory = new ConsoleResponseSenderDelegatorFactory();
        $this->assertSame(
            $listener->reveal(),
            $factory($container, 'SendResponseListener', $callback)
        );
    }
}

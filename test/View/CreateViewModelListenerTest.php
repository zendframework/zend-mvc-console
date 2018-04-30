<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\View;

use PHPUnit\Framework\TestCase;
use Zend\EventManager\EventManager;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\Mvc\Console\View\CreateViewModelListener;
use Zend\Mvc\Console\View\ViewModel as ConsoleModel;
use Zend\Mvc\MvcEvent;

class CreateViewModelListenerTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    public function setUp()
    {
        $this->listener = new CreateViewModelListener();
    }

    public function testAttachesListenersAtExpectedPriorities()
    {
        $events = new EventManager();
        $this->listener->attach($events);

        $this->assertListenerAtPriority(
            [$this->listener, 'createViewModelFromString'],
            -80,
            MvcEvent::EVENT_DISPATCH,
            $events,
            'View model from string listener not found'
        );

        $this->assertListenerAtPriority(
            [$this->listener, 'createViewModelFromArray'],
            -80,
            MvcEvent::EVENT_DISPATCH,
            $events,
            'View model from array listener not found'
        );

        $this->assertListenerAtPriority(
            [$this->listener, 'createViewModelFromNull'],
            -80,
            MvcEvent::EVENT_DISPATCH,
            $events,
            'View model from null listener not found'
        );
    }

    public function testCanDetachListenersFromEventManager()
    {
        $events = new EventManager();
        $this->listener->attach($events);

        $listeners = $this->getArrayOfListenersForEvent(MvcEvent::EVENT_DISPATCH, $events);
        $this->assertCount(3, $listeners);

        $this->listener->detach($events);
        $listeners = $this->getArrayOfListenersForEvent(MvcEvent::EVENT_DISPATCH, $events);
        $this->assertCount(0, $listeners);
    }

    public function testCanCreateViewModelFromStringResult()
    {
        $event = new MvcEvent();
        $event->setResult('content');
        $this->listener->createViewModelFromString($event);

        $result = $event->getResult();
        $this->assertInstanceOf(ConsoleModel::class, $result);
        $this->assertSame('content', $result->getVariable(ConsoleModel::RESULT));
    }

    public function testCanCreateViewModelFromArrayResult()
    {
        $expected = ['foo' => 'bar'];
        $event = new MvcEvent();
        $event->setResult($expected);
        $this->listener->createViewModelFromArray($event);

        $result = $event->getResult();
        $this->assertInstanceOf(ConsoleModel::class, $result);
        $this->assertSame($expected, $result->getVariables());
    }

    public function testCanCreateViewModelFromNullResult()
    {
        $event = new MvcEvent();
        $this->listener->createViewModelFromNull($event);

        $result = $event->getResult();
        $this->assertInstanceOf(ConsoleModel::class, $result);
    }
}

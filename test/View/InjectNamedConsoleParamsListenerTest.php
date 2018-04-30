<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\View;

use PHPUnit\Framework\TestCase;
use Zend\Console\Request;
use Zend\EventManager\EventManager;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\Mvc\Console\Router\RouteMatch;
use Zend\Mvc\Console\View\InjectNamedConsoleParamsListener;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\Parameters;

class InjectNamedConsoleParamsListenerTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    public function setUp()
    {
        $this->listener = new InjectNamedConsoleParamsListener();
    }

    public function testAttachesToEventManagerAtExpectedPriority()
    {
        $events = new EventManager();
        $this->listener->attach($events);

        $this->assertListenerAtPriority(
            [$this->listener, 'injectNamedParams'],
            -80,
            MvcEvent::EVENT_DISPATCH,
            $events,
            'Listener not attached at expected priority'
        );
    }

    public function testReturnsEarlyIfNoRouteMatchPresentInEvent()
    {
        $event = $this->prophesize(MvcEvent::class);
        $event->getRouteMatch()->willReturn(null);
        $event->getRequest()->shouldNotBeCalled();

        $this->assertNull($this->listener->injectNamedParams($event->reveal()));
    }

    public function testReturnsEarlyIfRequestIsNotFromConsoleEnvironment()
    {
        $routeMatch = $this->prophesize(RouteMatch::class);
        $routeMatch->getParams()->shouldNotBeCalled();

        $event = $this->prophesize(MvcEvent::class);
        $event->getRouteMatch()->willReturn($routeMatch->reveal());
        $event->getRequest()->willReturn(null);

        $this->assertNull($this->listener->injectNamedParams($event->reveal()));
    }

    public function testInjectsRequestWithRouteMatchParams()
    {
        $requestParams = $this->prophesize(Parameters::class);
        $requestParams->toArray()->willReturn([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'bat',
        ]);
        $requestParams->fromArray([
            'foo' => 'bar',
            'bar' => 'BAZ',
            'baz' => 'bat',
            'bat' => 'quz',
        ])->shouldBeCalled();

        $routeMatch = $this->prophesize(RouteMatch::class);
        $routeMatch->getParams()->willReturn([
            'bar' => 'BAZ',
            'bat' => 'quz',
        ]);

        $request = $this->prophesize(Request::class);
        $request->getParams()->willReturn($requestParams->reveal())->shouldBeCalledTimes(2);

        $event = $this->prophesize(MvcEvent::class);
        $event->getRouteMatch()->willReturn($routeMatch->reveal());
        $event->getRequest()->willReturn($request->reveal());

        $this->assertNull($this->listener->injectNamedParams($event->reveal()));
    }
}

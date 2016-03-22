<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\View;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventManager;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\Mvc\Console\Router\RouteMatch;
use Zend\Mvc\Console\View\InjectRouteMatchParamsListener;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\Parameters;

class InjectRouteMatchParamsListenerTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    public function setUp()
    {
        $this->listener = new InjectRouteMatchParamsListener();
    }

    public function testAttachesAtExpectedPriority()
    {
        $events = new EventManager();
        $this->listener->attach($events);
        $this->assertListenerAtPriority(
            [$this->listener, 'onDispatch'],
            91,
            MvcEvent::EVENT_DISPATCH,
            $events
        );
    }

    public function testOnDispatchDoesNothingIfEventDoesNotComposeConsoleRequest()
    {
        $event = $this->prophesize(MvcEvent::class);
        $event->getRequest()->willReturn(null);
        $event->getRouteMatch()->shouldNotBeenCalled();

        $this->assertNull($this->listener->onDispatch($event->reveal()));
    }

    public function testOnDispatchOverwritesParamsFromRouteMatchByDefault()
    {
        $params = new Parameters(['foo' => 'bar', 'baz' => 'bat']);
        $request = $this->prophesize(ConsoleRequest::class);
        $request->params()->willReturn($params);

        $routeMatch = $this->prophesize(RouteMatch::class);
        $routeMatch->getParams()->willReturn(['baz' => 'BAZ!', 'bat' => 'quz']);

        $event = $this->prophesize(MvcEvent::class);
        $event->getRequest()->willReturn($request->reveal());
        $event->getRouteMatch()->willReturn($routeMatch->reveal());

        $this->assertNull($this->listener->onDispatch($event->reveal()));

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'BAZ!',
            'bat' => 'quz',
        ], $params->toArray());
    }

    public function testCanRequestListenerDoesNotOverwriteRequestParams()
    {
        $params = new Parameters(['foo' => 'bar', 'baz' => 'bat']);
        $request = $this->prophesize(ConsoleRequest::class);
        $request->params()->willReturn($params);

        $routeMatch = $this->prophesize(RouteMatch::class);
        $routeMatch->getParams()->willReturn(['baz' => 'BAZ!', 'bat' => 'quz']);

        $event = $this->prophesize(MvcEvent::class);
        $event->getRequest()->willReturn($request->reveal());
        $event->getRouteMatch()->willReturn($routeMatch->reveal());

        $this->listener->setOverwrite(false);
        $this->assertNull($this->listener->onDispatch($event->reveal()));

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'bat',
            'bat' => 'quz',
        ], $params->toArray());
    }
}

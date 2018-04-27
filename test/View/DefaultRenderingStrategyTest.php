<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\View;

use PHPUnit\Framework\TestCase;
use Zend\Console\Adapter\AbstractAdapter;
use Zend\EventManager\EventManager;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\Mvc\ApplicationInterface;
use Zend\Mvc\Console\View\DefaultRenderingStrategy;
use Zend\Mvc\Console\View\Renderer;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Response;
use Zend\View\Model;

class DefaultRenderingStrategyTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var DefaultRenderingStrategy
     */
    protected $strategy;

    public function setUp()
    {
        $this->renderer = $this->prophesize(Renderer::class);
        $this->strategy = new DefaultRenderingStrategy($this->renderer->reveal());
    }

    public function testAttachesRendererAtExpectedPriority()
    {
        $events = new EventManager();
        $this->strategy->attach($events);
        $this->assertListenerAtPriority(
            [$this->strategy, 'render'],
            -10000,
            MvcEvent::EVENT_RENDER,
            $events,
            'Renderer listener not found'
        );
    }

    public function testCanDetachListenersFromEventManager()
    {
        $events = new EventManager();
        $this->strategy->attach($events);

        $listeners = $this->getArrayOfListenersForEvent(MvcEvent::EVENT_RENDER, $events);
        $this->assertCount(1, $listeners);

        $this->strategy->detach($events);
        $listeners = $this->getArrayOfListenersForEvent(MvcEvent::EVENT_RENDER, $events);
        $this->assertCount(0, $listeners);
    }

    public function testIgnoresNonConsoleModelNotContainingResultKeyWhenObtainingResult()
    {
        $console = $this->createMock(AbstractAdapter::class);
        $console
            ->expects($this->any())
            ->method('encodeText')
            ->willReturnArgument(0);

        //Register console service
        $sm = new ServiceManager();
        $sm->setService('console', $console);

        /* @var \PHPUnit_Framework_MockObject_MockObject|ApplicationInterface $mockApplication */
        $mockApplication = $this->createMock(ApplicationInterface::class);
        $mockApplication
            ->expects($this->any())
            ->method('getServiceManager')
            ->willReturn($sm);

        $event    = new MvcEvent();
        $event->setApplication($mockApplication);

        $model    = new Model\ViewModel(['content' => 'Page not found']);

        $this->renderer->render($model)->willReturn('');

        $response = new Response();
        $event->setResult($model);
        $event->setResponse($response);
        $this->strategy->render($event);
        $content = $response->getContent();
        $this->assertNotContains('Page not found', $content);
    }

    public function testIgnoresNonModel()
    {
        $console = $this->createMock(AbstractAdapter::class);
        $console
            ->expects($this->any())
            ->method('encodeText')
            ->willReturnArgument(0);

        //Register console service
        $sm = new ServiceManager();
        $sm->setService('console', $console);

        /* @var \PHPUnit_Framework_MockObject_MockObject|ApplicationInterface $mockApplication */
        $mockApplication = $this->createMock(ApplicationInterface::class);
        $mockApplication
            ->expects($this->any())
            ->method('getServiceManager')
            ->willReturn($sm);

        $event    = new MvcEvent();
        $event->setApplication($mockApplication);

        $model    = true;

        $this->renderer->render($model)->willReturn('');

        $response = new Response();
        $event->setResult($model);
        $event->setResponse($response);
        $this->assertSame($response, $this->strategy->render($event));
    }
}

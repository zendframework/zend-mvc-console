<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\View;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use ReflectionProperty;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Response as ConsoleResponse;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\SharedEventManager;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\Mvc\Application;
use Zend\Mvc\Console\Service\ConsoleExceptionStrategyFactory;
use Zend\Mvc\Console\Service\ConsoleRouteNotFoundStrategyFactory;
use Zend\Mvc\Console\Service\ConsoleViewManagerFactory;
use Zend\Mvc\Console\Service\DefaultRenderingStrategyFactory;
use Zend\Mvc\Console\View\CreateViewModelListener;
use Zend\Mvc\Console\View\DefaultRenderingStrategy;
use Zend\Mvc\Console\View\ExceptionStrategy;
use Zend\Mvc\Console\View\InjectNamedConsoleParamsListener;
use Zend\Mvc\Console\View\InjectViewModelListener;
use Zend\Mvc\Console\View\Renderer;
use Zend\Mvc\Console\View\RouteNotFoundStrategy;
use Zend\Mvc\Console\View\ViewManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Service\ServiceListenerFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\DispatchableInterface;
use Zend\View\View;

/**
 * Tests for {@see ViewManager}
 *
 * @covers \Zend\Mvc\Console\View\ViewManager
 */
class ViewManagerTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    /**
     * @var ServiceManager
     */
    private $services;

    /**
     * @var ServiceManagerConfig
     */
    private $config;

    /**
     * @var ConsoleViewManagerFactory
     */
    private $factory;

    public function setUp()
    {
        $this->services = new ServiceManager();
        $this->prepareServiceManagerConfig()->configureServiceManager($this->services);
        $this->factory  = new ConsoleViewManagerFactory();
    }

    public function setupBaseListenerExpectations($events, $shared, $container)
    {
        $routeNotFoundStrategy = $this->prophesize(RouteNotFoundStrategy::class);
        $routeNotFoundStrategy->attach($events->reveal())->shouldBeCalled();
        $container->get('ConsoleRouteNotFoundStrategy')->willReturn($routeNotFoundStrategy->reveal());

        $exceptionStrategy = $this->prophesize(ExceptionStrategy::class);
        $exceptionStrategy->attach($events->reveal())->shouldBeCalled();
        $container->get('ConsoleExceptionStrategy')->willReturn($exceptionStrategy->reveal());

        $defaultRenderingStrategy = $this->prophesize(DefaultRenderingStrategy::class);
        $defaultRenderingStrategy->attach($events->reveal())->shouldBeCalled();
        $container->get('ConsoleDefaultRenderingStrategy')->willReturn($defaultRenderingStrategy->reveal());

        $verifyCallable = function ($type, $method) {
            return function ($argument) use ($type, $method) {
                if (! is_array($argument)) {
                    return false;
                }
                if (! $argument[0] instanceof $type) {
                    return false;
                }
                if (! $argument[1] == $method) {
                    return false;
                }
                return true;
            };
        };

        $verifyInjectViewModelListener = $verifyCallable(InjectViewModelListener::class, 'injectViewModel');

        $events->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            Argument::that($verifyInjectViewModelListener),
            -100
        )->shouldBeCalled();

        $events->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            Argument::that($verifyInjectViewModelListener),
            -100
        )->shouldBeCalled();

        $shared->attach(
            DispatchableInterface::class,
            MvcEvent::EVENT_DISPATCH,
            Argument::that($verifyCallable(InjectNamedConsoleParamsListener::class, 'injectNamedParams')),
            1000
        )->shouldBeCalled();

        $shared->attach(
            DispatchableInterface::class,
            MvcEvent::EVENT_DISPATCH,
            Argument::that($verifyCallable(CreateViewModelListener::class, 'createViewModelFromArray')),
            -80
        )->shouldBeCalled();

        $shared->attach(
            DispatchableInterface::class,
            MvcEvent::EVENT_DISPATCH,
            Argument::that($verifyCallable(CreateViewModelListener::class, 'createViewModelFromString')),
            -80
        )->shouldBeCalled();

        $shared->attach(
            DispatchableInterface::class,
            MvcEvent::EVENT_DISPATCH,
            Argument::that($verifyCallable(CreateViewModelListener::class, 'createViewModelFromNull')),
            -80
        )->shouldBeCalled();

        $shared->attach(
            DispatchableInterface::class,
            MvcEvent::EVENT_DISPATCH,
            Argument::that($verifyCallable(InjectViewModelListener::class, 'injectViewModel')),
            -100
        )->shouldBeCalled();
    }

    /**
     * Create an event manager instance based on zend-eventmanager version
     *
     * @return EventManager
     */
    protected function createEventManager()
    {
        $r = new ReflectionClass(EventManager::class);

        if ($r->hasMethod('setSharedManager')) {
            $events = new EventManager();
            $events->setSharedManager(new SharedEventManager());
            return $events;
        }

        return new EventManager(new SharedEventManager());
    }

    private function prepareServiceManagerConfig()
    {
        $serviceListener = new ServiceListenerFactory();
        $r = new ReflectionProperty($serviceListener, 'defaultServiceConfig');
        $r->setAccessible(true);

        $config = $r->getValue($serviceListener);
        return new ServiceManagerConfig($config);
    }

    /**
     * @return array
     */
    public function viewManagerConfiguration()
    {
        return [
            'standard' => [
                [
                    'view_manager' => [
                        'display_exceptions' => false,
                        'display_not_found_reason' => false,
                    ],
                ]
            ],
            'with-console' => [
                [
                    'view_manager' => [
                        'display_exceptions' => true,
                        'display_not_found_reason' => true
                    ],
                    'console' => [
                        'view_manager' => [
                            'display_exceptions' => false,
                            'display_not_found_reason' => false,
                        ]
                    ]
                ]
            ],
            'without-console' => [
                [
                    'view_manager' => [
                        'display_exceptions' => false,
                        'display_not_found_reason' => false
                    ],
                ]
            ],
            'console-only' => [
                [
                    'console' => [
                        'view_manager' => [
                            'display_exceptions' => false,
                            'display_not_found_reason' => false
                        ]
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider viewManagerConfiguration
     *
     * @param array $config
     *
     * @group 6866
     */
    public function testConsoleKeyWillOverrideDisplayExceptionAndExceptionMessage($config)
    {
        $eventManager = $this->createEventManager();
        $request      = new ConsoleRequest();
        $response     = new ConsoleResponse();

        $this->services->setAllowOverride(true);
        $this->services->setService('config', $config);
        $this->services->setService('EventManager', $eventManager);
        $this->services->setService('Request', $request);
        $this->services->setService('Response', $response);
        $this->services->setFactory('ConsoleRouteNotFoundStrategy', ConsoleRouteNotFoundStrategyFactory::class);
        $this->services->setFactory('ConsoleExceptionStrategy', ConsoleExceptionStrategyFactory::class);
        $this->services->setFactory('ConsoleDefaultRenderingStrategy', DefaultRenderingStrategyFactory::class);
        $this->services->setFactory(Renderer::class, InvokableFactory::class);
        $this->services->setAllowOverride(false);

        $manager = $this->factory->__invoke($this->services, 'ConsoleViewRenderer');

        $application = new Application($this->services, $eventManager, $request, $response);

        $event = new MvcEvent();
        $event->setApplication($application);
        $manager->onBootstrap($event);

        $this->assertFalse($this->services->get('ConsoleExceptionStrategy')->displayExceptions());
        $this->assertFalse($this->services->get('ConsoleRouteNotFoundStrategy')->displayNotFoundReason());
    }

    /**
     * @group 6866
     */
    public function testConsoleDisplayExceptionIsTrue()
    {
        $eventManager = $this->createEventManager();
        $request      = new ConsoleRequest();
        $response     = new ConsoleResponse();

        $this->services->setAllowOverride(true);
        $this->services->setService('config', []);
        $this->services->setService('EventManager', $eventManager);
        $this->services->setService('Request', $request);
        $this->services->setService('Response', $response);
        $this->services->setFactory('ConsoleRouteNotFoundStrategy', ConsoleRouteNotFoundStrategyFactory::class);
        $this->services->setFactory('ConsoleExceptionStrategy', ConsoleExceptionStrategyFactory::class);
        $this->services->setFactory('ConsoleDefaultRenderingStrategy', DefaultRenderingStrategyFactory::class);
        $this->services->setFactory(Renderer::class, InvokableFactory::class);
        $this->services->setAllowOverride(false);

        $manager     = new ViewManager;
        $application = new Application($this->services, $eventManager, $request, $response);
        $event       = new MvcEvent();
        $event->setApplication($application);

        $manager->onBootstrap($event);

        $exceptionStrategy = $this->services->get('ConsoleExceptionStrategy');
        $this->assertInstanceOf(ExceptionStrategy::class, $exceptionStrategy);
        $this->assertTrue($exceptionStrategy->displayExceptions());

        $routeNotFoundStrategy = $this->services->get('ConsoleRouteNotFoundStrategy');
        $this->assertInstanceOf(RouteNotFoundStrategy::class, $routeNotFoundStrategy);
        $this->assertTrue($routeNotFoundStrategy->displayNotFoundReason());
    }

    public function testAttachesOnBootstrapListenerAtExpectedPriority()
    {
        $events = $this->createEventManager();
        $manager = new ViewManager();
        $manager->attach($events);

        $this->assertListenerAtPriority(
            [$manager, 'onBootstrap'],
            10000,
            MvcEvent::EVENT_BOOTSTRAP,
            $events
        );
    }

    public function testOnBootstrapAttachesExpectedListeners()
    {
        // Basic setup
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([]);

        $shared = $this->prophesize(SharedEventManagerInterface::class);
        $events = $this->prophesize(EventManagerInterface::class);
        $events->getSharedManager()->willReturn($shared->reveal());

        $application = $this->prophesize(Application::class);
        $application->getServiceManager()->willReturn($container->reveal());
        $application->getEventManager()->willReturn($events->reveal());

        $event = $this->prophesize(MvcEvent::class);
        $event->getApplication()->willReturn($application->reveal());

        $this->setupBaseListenerExpectations($events, $shared, $container);

        // Perform test
        $manager = new ViewManager();
        $this->assertNull($manager->onBootstrap($event->reveal()));
    }

    public function mvcStrategyConfiguration()
    {
        $baseConfig = [
            'view_manager' => [
                'mvc_strategies' => [],
            ],
        ];
        $baseStringConfig = $baseArrayConfig = $baseConfig;
        $baseStringConfig['view_manager']['mvc_strategies']  = 'CustomStrategy';
        $baseArrayConfig['view_manager']['mvc_strategies'][] = 'CustomStrategy';

        return [
            'console-string' => [['console' => $baseStringConfig], 'CustomStrategy'],
            'console-array'  => [['console' => $baseArrayConfig], 'CustomStrategy'],
            'default-string' => [$baseStringConfig, 'CustomStrategy'],
            'default-array'  => [$baseArrayConfig, 'CustomStrategy'],
        ];
    }

    /**
     * @dataProvider mvcStrategyConfiguration
     */
    public function testWillInjectAdditionalMvcStrategiesFromConfiguration($config, $listenerName)
    {
        // Basic setup
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config);

        $shared = $this->prophesize(SharedEventManagerInterface::class);
        $events = $this->prophesize(EventManagerInterface::class);
        $events->getSharedManager()->willReturn($shared->reveal());

        $application = $this->prophesize(Application::class);
        $application->getServiceManager()->willReturn($container->reveal());
        $application->getEventManager()->willReturn($events->reveal());

        $event = $this->prophesize(MvcEvent::class);
        $event->getApplication()->willReturn($application->reveal());

        $this->setupBaseListenerExpectations($events, $shared, $container);

        // Setup listener to attach
        $listener = $this->prophesize(ListenerAggregateInterface::class);
        $listener->attach($events->reveal(), 100)->shouldBeCalled();
        $container->get($listenerName)->willReturn($listener->reveal());

        // Perform test
        $manager = new ViewManager();
        $this->assertNull($manager->onBootstrap($event->reveal()));
    }

    public function viewStrategyConfiguration()
    {
        $baseConfig = [
            'view_manager' => [
                'strategies' => [],
            ],
        ];
        $baseStringConfig = $baseArrayConfig = $baseConfig;
        $baseStringConfig['view_manager']['strategies']  = 'CustomStrategy';
        $baseArrayConfig['view_manager']['strategies'][] = 'CustomStrategy';

        return [
            'console-string' => [['console' => $baseStringConfig], 'CustomStrategy'],
            'console-array'  => [['console' => $baseArrayConfig], 'CustomStrategy'],
            'default-string' => [$baseStringConfig, 'CustomStrategy'],
            'default-array'  => [$baseArrayConfig, 'CustomStrategy'],
        ];
    }

    /**
     * @dataProvider viewStrategyConfiguration
     */
    public function testWillRegisterViewStrategiesFromConfiguration($config, $strategyName)
    {
        // Basic setup
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config);

        $shared = $this->prophesize(SharedEventManagerInterface::class);
        $events = $this->prophesize(EventManagerInterface::class);
        $events->getSharedManager()->willReturn($shared->reveal());

        $application = $this->prophesize(Application::class);
        $application->getServiceManager()->willReturn($container->reveal());
        $application->getEventManager()->willReturn($events->reveal());

        $event = $this->prophesize(MvcEvent::class);
        $event->getApplication()->willReturn($application->reveal());

        $this->setupBaseListenerExpectations($events, $shared, $container);

        // Setup view
        $viewEvents = $this->prophesize(EventManagerInterface::class);
        $view = $this->prophesize(View::class);
        $view->getEventManager()->willReturn($viewEvents->reveal());
        $container->get(View::class)->willReturn($view->reveal());

        // Setup listener to attach
        $listener = $this->prophesize(ListenerAggregateInterface::class);
        $listener->attach($viewEvents->reveal(), 100)->shouldBeCalled();
        $container->get($strategyName)->willReturn($listener->reveal());

        // Perform test
        $manager = new ViewManager();
        $this->assertNull($manager->onBootstrap($event->reveal()));
    }

    /**
     * @group 14
     */
    public function testViewAccessorIsPublic()
    {
        $view = $this->prophesize(View::class)->reveal();
        $viewmanager = new ViewManager();
        $r = new ReflectionProperty($viewmanager, 'view');
        $r->setAccessible(true);
        $r->setValue($viewmanager, $view);

        $this->assertSame($view, $viewmanager->getView());
    }
}

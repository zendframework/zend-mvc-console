<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Console\Controller\Plugin\CreateConsoleNotFoundModel;
use Zend\Mvc\Console\Exception\InvalidArgumentException;
use Zend\Mvc\Console\View\ViewModel;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;
use Zend\ServiceManager\Factory\InvokableFactory;

class ConsoleControllerTest extends TestCase
{
    /**
     * @var TestAsset\ConsoleController
     */
    public $controller;

    public function setUp()
    {
        $this->controller = new TestAsset\ConsoleController();

        $plugins = $this->controller->getPluginManager();
        $plugins->setAlias('createConsoleNotFoundModel', CreateConsoleNotFoundModel::class);
        $plugins->setFactory(CreateConsoleNotFoundModel::class, InvokableFactory::class);

        $routeMatch = new RouteMatch(['controller' => 'controller-sample']);
        $event      = new MvcEvent();
        $event->setRouteMatch($routeMatch);
        $this->controller->setEvent($event);
    }

    public function testDispatchCorrectRequest()
    {
        $request = new ConsoleRequest();
        $result = $this->controller->dispatch($request);

        $this->assertNotNull($result);
    }

    public function testDispatchIncorrectRequest()
    {
        $request = new HttpRequest();

        $this->expectException(InvalidArgumentException::class);
        $this->controller->dispatch($request);
    }

    public function testGetNoInjectedConsole()
    {
        $console = $this->controller->getConsole();

        $this->assertNull($console);
    }

    public function testGetInjectedConsole()
    {
        $consoleAdapter = $this->createMock('\Zend\Console\Adapter\AdapterInterface');

        $controller = $this->controller->setConsole($consoleAdapter);
        $console = $this->controller->getConsole();

        $this->assertInstanceOf('\Zend\Mvc\Console\Controller\AbstractConsoleController', $controller);
        $this->assertInstanceOf('\Zend\Console\Adapter\AdapterInterface', $console);
    }

    public function testNotFoundActionInvokesCreateConsoleNotFoundModelPlugin()
    {
        $routeMatch = $this->prophesize(RouteMatch::class);
        $routeMatch->setParam('action', 'not-found')->shouldBeCalled();

        $event = $this->prophesize(MvcEvent::class);
        $event->getRouteMatch()->willReturn($routeMatch);

        $plugin = new CreateConsoleNotFoundModel();
        $plugins = $this->prophesize(PluginManager::class);
        $plugins->setController(Argument::type(TestAsset\ConsoleController::class))->shouldBeCalled();
        $plugins->get('createConsoleNotFoundModel', Argument::any())->willReturn($plugin);

        $controller = new TestAsset\ConsoleController();
        $controller->setEvent($event->reveal());
        $controller->setPluginManager($plugins->reveal());

        $result = $controller->notFoundAction();
        $this->assertInstanceOf(ViewModel::class, $result);
    }
}

<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Router;

use PHPUnit\Framework\TestCase;
use Zend\Console\Request;
use Zend\Mvc\Console\Router\Catchall;
use Zend\Mvc\Console\Router\RouteMatch;
use Zend\Stdlib\RequestInterface;

class CatchallTest extends TestCase
{
    public function provideFactoryOptions()
    {
        return [
            [[]],
            [['defaults' => []]]
        ];
    }

    /**
     * @dataProvider provideFactoryOptions
     */
    public function testFactoryReturnsInstanceForAnyOptionsArray($options)
    {
        $this->assertInstanceOf(Catchall::class, Catchall::factory($options));
    }

    public function testMatchReturnsEarlyForNonConsoleRequests()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $route = new Catchall();
        $this->assertNull($route->match($request));
    }

    public function testMatchReturnsConstructorParamsForConsoleRequests()
    {
        $params = ['foo' => 'bar'];
        $request = $this->prophesize(Request::class)->reveal();
        $route = new Catchall($params);
        $result = $route->match($request);
        $this->assertInstanceOf(RouteMatch::class, $result);
        $this->assertEquals($params, $result->getParams());
    }

    public function testAssembleClearsAssembledParams()
    {
        $route = new Catchall();
        $route->assemble();
        $this->assertEquals([], $route->getAssembledParams());
    }
}

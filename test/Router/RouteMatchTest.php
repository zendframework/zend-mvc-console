<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Router;

use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Router\RouteMatch;

class RouteMatchTest extends TestCase
{
    public function testConstructorCanAcceptArgumentLength()
    {
        $routeMatch = new RouteMatch(['foo' => true], 5);
        $this->assertEquals(5, $routeMatch->getLength());
    }

    public function testSettingMatchedRouteNameForFirstTimeSetsItVerbatim()
    {
        $routeMatch = new RouteMatch(['foo' => true], 5);
        $routeMatch->setMatchedRouteName('foo');
        $this->assertEquals('foo', $routeMatch->getMatchedRouteName());
        return $routeMatch;
    }

    /**
     * @depends testSettingMatchedRouteNameForFirstTimeSetsItVerbatim
     */
    public function testSettingMatchedRouteNameSubsequentTimePrependsNewName($routeMatch)
    {
        $routeMatch->setMatchedRouteName('bar');
        $this->assertEquals('bar/foo', $routeMatch->getMatchedRouteName());
    }

    public function testAllowsMergingWithAnotherInstance()
    {
        $first = new RouteMatch(['foo' => true], 5);
        $second = new RouteMatch(['bar' => 'baz'], 9);

        $merged = $first->merge($second);
        $this->assertSame($first, $merged);
        $this->assertEquals(14, $merged->getLength());
        $this->assertEquals($second->getMatchedRouteName(), $merged->getMatchedRouteName());
        $this->assertEquals([
            'foo' => true,
            'bar' => 'baz',
        ], $merged->getParams());
    }
}

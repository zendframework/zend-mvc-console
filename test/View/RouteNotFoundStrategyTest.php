<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace ZendTest\Mvc\Console\View;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use Zend\Mvc\Console\View\RouteNotFoundStrategy;

class RouteNotFoundStrategyTest extends TestCase
{
    /**
     * @var RouteNotFoundStrategy
     */
    protected $strategy;

    public function setUp()
    {
        $this->strategy = new RouteNotFoundStrategy();
    }

    public function testRenderTableConcatenateAndInvalidInputDoesNotThrowException()
    {
        $reflection = new ReflectionClass(RouteNotFoundStrategy::class);
        $method = $reflection->getMethod('renderTable');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->strategy, [[[]], 1, 0]);
        $this->assertSame('', $result);
    }
}

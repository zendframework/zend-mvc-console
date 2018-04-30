<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Router;

use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Router\Catchall;
use Zend\Mvc\Console\Router\Simple;
use Zend\Mvc\Console\Router\SimpleRouteStack;

class SimpleRouteStackTest extends TestCase
{
    public function routeTypeProvider()
    {
        $catchallOpts = ['defaults' => []];
        $simpleOpts   = ['route' => 'test'];

        return [
            'catchall' => ['catchall', $catchallOpts, Catchall::class],
            'catchAll' => ['catchAll', $catchallOpts, Catchall::class],
            'Catchall' => ['Catchall', $catchallOpts, Catchall::class],
            'CatchAll' => ['CatchAll', $catchallOpts, Catchall::class],
            'simple'   => ['simple', $simpleOpts, Simple::class],
            'Simple'   => ['Simple', $simpleOpts, Simple::class],

            Catchall::class => [Catchall::class, $catchallOpts, Catchall::class],
            Simple::class   => [Simple::class, $simpleOpts, Simple::class],
        ];
    }

    /**
     * @dataProvider routeTypeProvider
     */
    public function testExpectedAliasesAndFactoriesResolve($serviceName, array $options, $expected)
    {
        $router = new SimpleRouteStack();
        $routes = $router->getRoutePluginManager();
        $this->assertInstanceOf($expected, $routes->get($serviceName, $options));
    }
}

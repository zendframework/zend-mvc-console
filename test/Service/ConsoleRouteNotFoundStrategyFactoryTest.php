<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Service\ConsoleRouteNotFoundStrategyFactory;
use Zend\Mvc\Console\View\RouteNotFoundStrategy;

class ConsoleRouteNotFoundStrategyFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new ConsoleRouteNotFoundStrategyFactory();
    }

    public function testDisplaysRouteNotFoundReasonByDefault()
    {
        $this->container->has('config')->willReturn(false);

        $strategy = $this->factory->__invoke($this->container->reveal(), RouteNotFoundStrategy::class);
        $this->assertInstanceOf(RouteNotFoundStrategy::class, $strategy);
        $this->assertTrue($strategy->displayNotFoundReason());
    }

    public function overrideDisplayNotFoundReasonConfig()
    {
        return [
            'console' => [[
                'console' => ['view_manager' => [
                    'display_not_found_reason' => false,
                ]],
            ]],
            'default' => [[
                'view_manager' => [
                    'display_not_found_reason' => false,
                ],
            ]],
        ];
    }

    /**
     * @dataProvider overrideDisplayNotFoundReasonConfig
     */
    public function testCanToggleDisplayRouteNotFoundReasonFlagViaConfiguration($config)
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $strategy = $this->factory->__invoke($this->container->reveal(), RouteNotFoundStrategy::class);
        $this->assertInstanceOf(RouteNotFoundStrategy::class, $strategy);
        $this->assertFalse($strategy->displayNotFoundReason());
    }
}

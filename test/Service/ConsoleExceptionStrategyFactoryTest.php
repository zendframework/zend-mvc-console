<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Service\ConsoleExceptionStrategyFactory;
use Zend\Mvc\Console\View\ExceptionStrategy;

class ConsoleExceptionStrategyFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new ConsoleExceptionStrategyFactory();
    }

    public function testEnablesDisplayExceptionsWithoutConfiguration()
    {
        $this->container->has('config')->willReturn(false);

        $strategy = $this->factory->__invoke($this->container->reveal(), ExceptionStrategy::class);
        $this->assertInstanceOf(ExceptionStrategy::class, $strategy);
        $this->assertTrue($strategy->displayExceptions());
    }

    public function testProvidesDefaultExceptionMessageWithoutConfiguration()
    {
        $this->container->has('config')->willReturn(false);

        $strategy = $this->factory->__invoke($this->container->reveal(), ExceptionStrategy::class);
        $this->assertInstanceOf(ExceptionStrategy::class, $strategy);
        $plainStrategy = new ExceptionStrategy();
        $this->assertEquals($plainStrategy->getMessage(), $strategy->getMessage());
    }

    public function overrideDisplayExceptionsConfiguration()
    {
        return [
            'console' => [[
                'console' => ['view_manager' => [
                    'display_exceptions' => false,
                ]]
            ]],
            'default' => [[
                'view_manager' => [
                    'display_exceptions' => false,
                ]
            ]],
        ];
    }

    /**
     * @dataProvider overrideDisplayExceptionsConfiguration
     */
    public function testCanOverrideDisplayExceptionsFlagViaConfiguration($config)
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $strategy = $this->factory->__invoke($this->container->reveal(), ExceptionStrategy::class);
        $this->assertInstanceOf(ExceptionStrategy::class, $strategy);
        $this->assertFalse($strategy->displayExceptions());
    }

    public function overrideExceptionMessageConfiguration()
    {
        return [
            'console' => [[
                'console' => ['view_manager' => [
                    'exception_message' => 'MESSAGE',
                ]]
            ], 'MESSAGE'],
            'default' => [[
                'view_manager' => [
                    'exception_message' => 'MESSAGE',
                ]
            ], 'MESSAGE'],
        ];
    }

    /**
     * @dataProvider overrideExceptionMessageConfiguration
     */
    public function testCanOverrideExceptionMessageViaConfiguration($config, $expected)
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $strategy = $this->factory->__invoke($this->container->reveal(), ExceptionStrategy::class);
        $this->assertInstanceOf(ExceptionStrategy::class, $strategy);
        $this->assertEquals($expected, $strategy->getMessage());
    }
}

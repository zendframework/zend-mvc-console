<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Service\DefaultRenderingStrategyFactory;
use Zend\Mvc\Console\View\DefaultRenderingStrategy;
use Zend\Mvc\Console\View\Renderer;

class DefaultRenderingStrategyFactoryTest extends TestCase
{
    public function testReturnsDefaultRenderingStrategyWithRendererInjected()
    {
        $renderer = $this->prophesize(Renderer::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Renderer::class)->willReturn($renderer);

        $factory = new DefaultRenderingStrategyFactory();
        $result = $factory($container->reveal(), DefaultRenderingStrategy::class);
        $this->assertInstanceOf(DefaultRenderingStrategy::class, $result);
    }
}

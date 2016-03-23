<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\Console\Controller\Plugin\CreateConsoleNotFoundModel;
use Zend\Mvc\Console\Service\ControllerPluginManagerDelegatorFactory;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

class ControllerPluginManagerDelegatorFactoryTest extends TestCase
{
    public function testReturnsPluginManagerWithConfigurationForCreateConsoleNotFoundModelPlugin()
    {
        $plugins = $this->prophesize(PluginManager::class);
        $plugins->setAlias('CreateConsoleNotFoundModel', CreateConsoleNotFoundModel::class)->shouldBeCalled();
        $plugins->setAlias('createConsoleNotFoundModel', CreateConsoleNotFoundModel::class)->shouldBeCalled();
        $plugins->setAlias('createconsolenotfoundmodel', CreateConsoleNotFoundModel::class)->shouldBeCalled();
        $plugins->setFactory(CreateConsoleNotFoundModel::class, InvokableFactory::class)->shouldBeCalled();

        $callback = function () use ($plugins) {
            return $plugins->reveal();
        };

        $factory = new ControllerPluginManagerDelegatorFactory();
        $this->assertSame($plugins->reveal(), $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
            'ControllerPluginManager',
            $callback
        ));
    }
}

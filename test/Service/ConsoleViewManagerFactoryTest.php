<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Service\ConsoleViewManagerFactory;
use Zend\Mvc\Console\View\ViewManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class ConsoleViewManagerFactoryTest extends TestCase
{
    use FactoryEnvironmentTrait;

    public function testRaisesExceptionWhenNotInConsoleEnvironment()
    {
        $this->setConsoleEnvironment(false);

        $factory = new ConsoleViewManagerFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('requires a Console environment');
        $factory($this->createContainer(), 'ConsoleViewManager');
    }

    public function testReturnsViewManagerWhenInConsoleEnvironment()
    {
        $this->setConsoleEnvironment(true);

        $factory = new ConsoleViewManagerFactory();
        $result = $factory($this->createContainer(), 'ConsoleViewManager');
        $this->assertInstanceOf(ViewManager::class, $result);
    }
}

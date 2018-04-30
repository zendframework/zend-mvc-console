<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Service;

use PHPUnit\Framework\TestCase;
use Zend\Console\Response;
use Zend\Mvc\Console\Service\ConsoleResponseDelegatorFactory;

class ConsoleResponseDelegatorFactoryTest extends TestCase
{
    use FactoryEnvironmentTrait;

    public function setUp()
    {
        $this->factory = new ConsoleResponseDelegatorFactory();
    }

    public function testReturnsReturnValueOfCallbackWhenNotInConsoleEnvironment()
    {
        $this->setConsoleEnvironment(false);

        $callback = function () {
            return 'FOO';
        };

        $this->assertSame(
            $callback(),
            $this->factory->__invoke($this->createContainer(), 'Response', $callback)
        );
    }

    public function testReturnsConsoleResponseWhenInConsoleEnvironment()
    {
        $this->setConsoleEnvironment(true);

        $callback = function () {
            return 'FOO';
        };

        $result = $this->factory->__invoke($this->createContainer(), 'Response', $callback);
        $this->assertInstanceOf(Response::class, $result);
    }
}

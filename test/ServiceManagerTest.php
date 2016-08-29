<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\Mvc\Console\ResponseSender\ConsoleResponseSender;
use Zend\Mvc\Console\Service\ConsoleResponseSenderDelegatorFactory;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\Mvc\SendResponseListener;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Factory\InvokableFactory;

use ReflectionProperty;

class ServiceManagerTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    public function testEventManagerOverridden()
    {
        $minimalConfig = [
            'aliases' => [
                'SendResponseListener' => SendResponseListener::class,
            ],
            'factories' => [
                SendResponseListener::class => InvokableFactory::class
            ],
            'delegators' => [
                SendResponseListener::class => [
                    function (ContainerInterface $container, $name, callable $callback, array $options = null) {
                        $consoleResponseSenderDelegatorFactory = new ConsoleResponseSenderDelegatorFactory();
                        $sendResponseListener = $consoleResponseSenderDelegatorFactory->__invoke($container, $name, $callback, $options);
                        $this->assertInstanceOf(SendResponseListener::class, $sendResponseListener);

                        $eventManager = $sendResponseListener->getEventManager();
                        $this->assertEvents($eventManager);

                        return $sendResponseListener;
                    }
                ],
            ]
        ];

        $smConfig = new ServiceManagerConfig($minimalConfig);

        $serviceManager = new ServiceManager();
        $smConfig->configureServiceManager($serviceManager);

        $sendResponseListener = $serviceManager->get('SendResponseListener');
        $eventManager = $sendResponseListener->getEventManager();
        $this->assertEvents($eventManager);
    }

    protected function assertEvents($eventManager)
    {
        $count = 0;
        $found = false;

        foreach ($this->getListenersForEvent(SendResponseEvent::EVENT_SEND_RESPONSE, $eventManager, true) as $priority => $listener) {
            $count++;
            if ($priority === -2000
                && $listener instanceof ConsoleResponseSender
            ) {
                $found = true;
            }
        }

        $this->assertEquals(4, $count);
        $this->assertTrue($found, 'ConsoleResponseSender was not found in listeners');
    }
}

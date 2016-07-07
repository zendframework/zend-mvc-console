<?php
namespace ZendTest\Mvc\Console;

use PHPUnit_Framework_TestCase as TestCase;

use Interop\Container\ContainerInterface;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Factory\InvokableFactory;

use Zend\Mvc\SendResponseListener;
use Zend\Mvc\Service\ServiceManagerConfig;

use Zend\Mvc\Console\ResponseSender\ConsoleResponseSender;
use Zend\Mvc\Console\Service\ConsoleResponseSenderDelegatorFactory;

use ReflectionProperty;

class ServiceManagerTest extends TestCase
{
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
        $r = new ReflectionProperty($eventManager, 'events');
        $r->setAccessible(true);
        $events = $r->getValue($eventManager);

        $this->assertEquals(4, count($events['sendResponse']));
        $this->assertEquals(ConsoleResponseSender::class, get_class($events['sendResponse']['-2000.0'][0]));
    }
}

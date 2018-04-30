<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console;

use PHPUnit\Framework\TestCase;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\Mvc\Console\ResponseSender\ConsoleResponseSender;
use Zend\Mvc\Console\Service\ConsoleResponseSenderDelegatorFactory;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\Mvc\SendResponseListener;
use Zend\Mvc\Service\SendResponseListenerFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

class ServiceManagerTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    /**
     * @group 10
     * @group 11
     * @group 12
     */
    public function testEventManagerOverridden()
    {
        $minimalConfig = [
            'aliases' => [
                'SendResponseListener' => SendResponseListener::class,
            ],
            'factories' => [
                SendResponseListener::class => SendResponseListenerFactory::class,
            ],
            'delegators' => [
                SendResponseListener::class => [
                    ConsoleResponseSenderDelegatorFactory::class,
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
        $listeners = $this->getListenersForEvent(SendResponseEvent::EVENT_SEND_RESPONSE, $eventManager, true);

        foreach ($listeners as $priority => $listener) {
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

<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\ResponseSender;

use PHPUnit\Framework\TestCase;
use Zend\Console\Response;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\Stdlib\ResponseInterface;
use ZendTest\Mvc\Console\ResponseSender\TestAsset\ConsoleResponseSender;

class ConsoleResponseSenderTest extends TestCase
{
    public function testSendResponseIgnoresInvalidResponseTypes()
    {
        $mockResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        $mockSendResponseEvent = $this->getSendResponseEventMock($mockResponse);
        $responseSender = new ConsoleResponseSender();
        ob_start();
        $result = $responseSender($mockSendResponseEvent);
        $body = ob_get_clean();
        $this->assertEquals('', $body);
        $this->assertNull($result);
    }

    public function testSendResponseTwoTimesPrintsResponseOnceAndReturnsErrorLevel()
    {
        $returnValue = false;
        $mockResponse = $this->createMock(Response::class);
        $mockResponse
            ->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue('body'));
        $mockResponse
            ->expects($this->exactly(2))
            ->method('getMetadata')
            ->with('errorLevel', 0)
            ->will($this->returnValue(0));

        $mockSendResponseEvent = $this->getSendResponseEventMock($mockResponse);
        $mockSendResponseEvent
            ->expects($this->once())
            ->method('setContentSent');
        $mockSendResponseEvent
            ->expects($this->any())
            ->method('contentSent')
            ->will($this->returnCallback(function () use (&$returnValue) {
                if (false === $returnValue) {
                    $returnValue = true;
                    return false;
                }
                return true;
            }));
        $responseSender = new ConsoleResponseSender();
        ob_start();
        $result = $responseSender($mockSendResponseEvent);
        $body = ob_get_clean();
        $this->assertEquals('body', $body);
        $this->assertEquals(0, $result);

        ob_start();
        $result = $responseSender($mockSendResponseEvent);
        $body = ob_get_clean();
        $this->assertEquals('', $body);
        $this->assertEquals(0, $result);
    }

    protected function getSendResponseEventMock($response)
    {
        $mockSendResponseEvent = $this->getMockBuilder(SendResponseEvent::class)
            ->setMethods(['getResponse', 'contentSent', 'setContentSent'])
            ->getMock();
        $mockSendResponseEvent->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        return $mockSendResponseEvent;
    }

    public function testInvocationReturnsEarlyIfResponseIsNotAConsoleResponse()
    {
        $event = $this->prophesize(SendResponseEvent::class);
        $event->getResponse()->willReturn(null)->shouldBeCalledTimes(1);

        $sender = new ConsoleResponseSender();
        $this->assertNull($sender($event->reveal()));
    }
}

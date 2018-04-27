<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\View;

use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\View\ViewModel;

class ViewModelTest extends TestCase
{
    public function setUp()
    {
        $this->model = new ViewModel();
    }

    public function testCaptureToIsNullByDefault()
    {
        $this->assertNull($this->model->captureTo());
    }

    public function testTerminalByDefault()
    {
        $this->assertTrue($this->model->terminate());
    }

    public function testErrorLevelIsNotSetByDefault()
    {
        $this->assertNull($this->model->getErrorLevel());
    }

    public function testCanSetErrorLevel()
    {
        $this->model->setErrorLevel(E_USER_DEPRECATED);
        $this->assertSame(E_USER_DEPRECATED, $this->model->getErrorLevel());
    }

    public function testResultIsNullByDefault()
    {
        $this->assertNull($this->model->getResult());
    }

    public function testCanSetResult()
    {
        $this->model->setResult('FOO');
        $this->assertEquals('FOO', $this->model->getResult());
    }
}

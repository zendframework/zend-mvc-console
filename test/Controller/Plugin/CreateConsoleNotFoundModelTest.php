<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Console\Controller\Plugin;

use PHPUnit\Framework\TestCase;
use Zend\Mvc\Console\Controller\Plugin\CreateConsoleNotFoundModel;
use Zend\Mvc\Console\View\ViewModel as ConsoleModel;

class CreateConsoleNotFoundModelTest extends TestCase
{
    public function testCanReturnModelWithErrorMessageAndErrorLevel()
    {
        $plugin = new CreateConsoleNotFoundModel();
        $model  = $plugin();

        $this->assertInstanceOf(ConsoleModel::class, $model);
        $this->assertSame('Page not found', $model->getResult());
        $this->assertSame(1, $model->getErrorLevel());
    }
}

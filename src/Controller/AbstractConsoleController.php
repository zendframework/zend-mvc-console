<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Console\Controller;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Console\Exception\InvalidArgumentException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class AbstractConsoleController extends AbstractActionController
{
    /**
     * @var ConsoleAdapter
     */
    protected $console;

    /**
     * @param ConsoleAdapter $console
     */
    public function setConsole(ConsoleAdapter $console)
    {
        $this->console = $console;
        return $this;
    }

    /**
     * @return ConsoleAdapter
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        if (! $request instanceof ConsoleRequest) {
            throw new InvalidArgumentException(sprintf(
                '%s can only dispatch requests in a console environment',
                get_called_class()
            ));
        }
        return parent::dispatch($request, $response);
    }
}

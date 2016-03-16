<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright  Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Console;

use Zend\Mvc\SendResponseListener;
use Zend\Mvc\Service;
use Zend\Mvc\View\Console\DefaultRenderingStrategy;
use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    /**
     * Provide configuration for this component.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Provide dependency configuration for this component.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'aliases' => [
                'ConsoleDefaultRenderingStrategy' => DefaultRenderingStrategy::class,
            ],
            'delegator_factories' => [
                'Application'               => [ Service\ConsoleApplicationDelegatorFactory::class ],
                'Request'                   => [ Service\ConsoleRequestDelegatorFactory::class ],
                'Response'                  => [ Service\ConsoleResponseDelegatorFactory::class ],
                'Router'                    => [ Service\ConsoleRouterDelegatorFactory::class ],
                SendResponseListener::class => [ Service\ConsoleResponseSenderDelegatorFactory::class ],
                'ViewHelperManager'         => [ Service\ConsoleViewHelperManagerDelegatorFactory::class ],
            ],
            'factories' => [
                'ConsoleAdapter'                => Service\ConsoleAdapterFactory::class,
                'ConsoleExceptionStrategy'      => Service\ConsoleExceptionStrategyFactory::class,
                'ConsoleRouteNotFoundStrategy'  => Service\ConsoleRouteNotFoundStrategyFactory::class,
                'ConsoleRouter'                 => Service\ConsoleRouterFactory::class,
                'ConsoleViewManager'            => Service\ConsoleViewManagerFactory::class,
                DefaultRenderingStrategy::class => InvokableFactory::class,
            ],
        ];
    }
}

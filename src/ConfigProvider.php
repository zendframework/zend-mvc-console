<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Console;

use Zend\Mvc\SendResponseListener;
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
                'ConsoleDefaultRenderingStrategy' => View\DefaultRenderingStrategy::class,
            ],
            'delegator_factories' => [
                'Application'               => [ Service\ConsoleApplicationDelegatorFactory::class ],
                'Request'                   => [ Service\ConsoleRequestDelegatorFactory::class ],
                'Response'                  => [ Service\ConsoleResponseDelegatorFactory::class ],
                'Router'                    => [ Router\ConsoleRouterDelegatorFactory::class ],
                SendResponseListener::class => [ Service\ConsoleResponseSenderDelegatorFactory::class ],
                'ViewHelperManager'         => [ Service\ConsoleViewHelperManagerDelegatorFactory::class ],
            ],
            'factories' => [
                'ConsoleAdapter'               => Service\ConsoleAdapterFactory::class,
                'ConsoleExceptionStrategy'     => Service\ConsoleExceptionStrategyFactory::class,
                'ConsoleRouteNotFoundStrategy' => Service\ConsoleRouteNotFoundStrategyFactory::class,
                'ConsoleRouter'                => Router\ConsoleRouterFactory::class,
                'ConsoleViewManager'           => Service\ConsoleViewManagerFactory::class,
                View\DefaultRenderingStrategy::class => InvokableFactory::class,
            ],
        ];
    }
}

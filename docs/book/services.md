# Default Services

zend-mvc-console exists to enable legacy console tooling for zend-mvc
applications. As such, one of its primary functions is providing services to the
MVC layer.

This chapter details the various services registered by zend-mvc-console by
default, the classes they represent, and any configuration options available.

## Services Provided

The following is a list of service names and what the service returns.

Service Name                                     | Creates instance of
------------------------------------------------ | -------------------
ConsoleAdapter                                   | `Zend\Console\Adapter\AdapterInterface`
ConsoleExceptionStrategy                         | `Zend\Mvc\Console\View\ExceptionStrategy`
ConsoleRouteNotFoundStrategy                     | `Zend\Mvc\Console\View\RouteNotFoundStrategy`
ConsoleRouter                                    | `Zend\Mvc\Console\Router\SimpleRouteStack`
ConsoleViewManager                               | `Zend\Mvc\Console\View\ViewManager`
`Zend\Mvc\Console\View\DefaultRenderingStrategy` | `Zend\Mvc\Console\View\DefaultRenderingStrategy`
`Zend\Mvc\Console\View\Renderer`                 | `Zend\Mvc\Console\View\Renderer`

## Aliases

The following is a list of service aliases.

Alias                           | Aliased to
------------------------------- | ----------
ConsoleDefaultRenderingStrategy | `Zend\Mvc\Console\View\DefaultRenderingStrategy`
ConsoleRenderer                 | `Zend\Mvc\Console\View\Renderer`

## Delegator factories

When operating in a console environment, several typical zend-mvc services need
to operate differently, or require alternate services. To enable that,
zend-mvc-console provides a number of [delegator
factories](http://docs.zendframework.com/zend-servicemanager/delegators/). The
following is a list of those provided, the service they override, and a
description of what they do.

Service Name                    | Delegator Factory                                                   | Description
------------------------------- | ------------------------------------------------------------------- | -----------
Application                     | `Zend\Mvc\Console\Service\ConsoleApplicationDelegatorFactory`       | In a console environment, attaches the `Zend\Mvc\Console\View\ViewManager` to the application instance before returning it.
ControllerManager               | `Zend\Mvc\Console\Service\ControllerManagerDelegatorFactory`        | Attaches an initializer for injecting `AbstractConsoleController` instances with a console adapter.
ControllerPluginManager         | `Zend\Mvc\Console\Service\ControllerPluginManagerDelegatorFactory`  | Injects the `CreateConsoleNotFoundModel` plugin into the controller `PluginManager`.
Request                         | `Zend\Mvc\Console\Service\ConsoleRequestDelegatorFactory`           | If a console environment is detected, replaces the request with a `Zend\Console\Request`.
Response                        | `Zend\Mvc\Console\Service\ConsoleResponseDelegatorFactory`          | If a console environment is detected, replaces the response with a `Zend\Console\Response`.
Router                          | `Zend\Mvc\Console\Router\ConsoleRouterDelegatorFactory`             | If a console environment is detected, replaces the router with the `ConsoleRouter` service.
`Zend\Mvc\SendResponseListener` | `Zend\Mvc\Console\Service\ConsoleResponseSenderDelegatorFactory`    | If a console environment is detected, attaches the `Zend\Mvc\Console\ResponseSender\ConsoleResponseSender` to the `SendResponseListener`.
ViewHelperManager               | `Zend\Mvc\Console\Service\ConsoleViewHelperManagerDelegatorFactory` | If a console environment is detected, injects override factories for the `url` and `basePath` view helpers into the `HelperPluginManager`.

## Application Configuration Options

Console tooling provides several locations for configuration, primarily at the
service, routing, and view levels.

### Services

All services registered can be configured to use different factories; see the
above tables for details on what service names to override.

### Routing

Routing configuration is detailed in the [routing chapter](routing.md).

### ViewManager

`Zend\Mvc\Console\View\ViewManager` acts similarly to its [zend-mvc
equivalent](http://docs.zendframework.com/zend-mvc/services/#viewmanager), and
will look for one or the other of the following configuration structures:

```php
return [
    'view_manager' => [
        'mvc_strategies' => $stringOrArrayOfMvcListenerServiceNames,
        'strategies'     => $stringOrArrayOfViewListenerServiceNames,
    ],
    'console'      => [
        'view_manager' => [
            'mvc_strategies' => $stringOrArrayOfMvcListenerServiceNames,
            'strategies'     => $stringOrArrayOfViewListenerServiceNames,
        ],
    ],
];
```

Preference is given to those under the `console` top-level key (those under
`view_manager` are ignored if the `console.view_manager` structure exists).

`mvc_strategies` refers to view-related listeners that need to operate on the
`Zend\Mvc\MvcEvent` context. `strategies` refers to view-related listeners that operate
on the `Zend\View\ViewEvent` context.

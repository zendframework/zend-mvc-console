# Introduction

Zend Framework 2 introduce the ability to write console applications via its MVC
layer. This ability integrates a number of components, including:

- zend-console
- zend-eventmanager
- zend-modulemanager
- zend-servicemanager
- zend-stdlib
- zend-text
- zend-view

When correctly configured, the functionality allows you to execute console
applications via the same `public/index.php` script as used for HTTP requests:

```bash
$ php public/index.php <arguments...>
```

For version 3 we have separated the functionality into a separate integration
component, zend-mvc-console. You can install and activate it via composer:

```bash
$ composer require zendframework/zend-mvc-console
```

Assuming you are using the [component
installer](https://docs.zendframework.com/zend-component-installer], doing so
will enable the component in your application, allowing you to immediately start
developing console applications via your MVC.

> ## Manual installation
>
> If you are not using the component installer, you will need to add this
> component as a module at the start of your module list in your Zend Framework
> application.
>
> If using a vanilla skeleton application, update `config/application.config.php`:
>
> ```php
> 'modules' => [
>     'Zend\Mvc\Console', // add it to the top of your module list
>     /* ... */
> ]
> ```
>
> If using Apigility, update your `config/modules.config.php`:
>
> ```php
> return [
>     'Zend\Mvc\Console', // add it to the top of your module list
>     /* ... */
> ];
> ```

## Deprecated

Due to the amount of integration required to support console tooling via the
MVC, and because [better, more standalone solutions
exist](https://github.com/zfcampus/zf-console), we will not be maintaining
zend-mvc-console long term. We strongly urge developers to start migrating their
MVC-based console tooling to use other libraries, such as
[zf-console](https://github.com/zfcampus/zf-console).

## Migration

In order to separate the console tooling from zend-mvc and provide it as a
standalone package, we needed to make a few changes. See the
[migration guide](migration/v2-to-v3.md) for details.

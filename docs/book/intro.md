# Introduction

Zend Framework 2 introduced the ability to write console applications via its MVC
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

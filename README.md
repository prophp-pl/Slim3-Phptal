# Slim 3 Framework PHPTAL View

This is a Slim Framework view helper built on top of the PHPTAL template engine.

Requires Slim 3 Framework

Package contains full working example.

## Install

From inside main folder:

```bash
$ composer install
```

Also remember to set folder for compiled templates. This can be configured inside `app/config/settings.php`. Default folder is `/temp/phptal` and compiled files have `.php` extension.

If You need direct access to PHPTAL engine, just call `$this->view->getEngine()` method. Variables can be set in the following way:

```php
$this->view->var = 1;
$this->view->getEngine()->var = 1;
$this->view['var'] = 1;
$this->view->assign('var', 1);
```

To get all assigned variables call `$this->view->getIterator()` which will return ArrayIterator.

## License

The MIT License (MIT).

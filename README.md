## Laravel Http Logger

### Installation
```
composer require zipzoft/laravel-http-logger
```

### Config
```
php artisan vendor:publish --provider="Zipzoft\HttpLogger\HttpLoggerServiceProvider" --tag="config"
```
Configuration will be added to config/http-logger.php


### Usage
Add the middleware as Global or single route
```php
// in `app/Http/Kernel.php`
protected $middleware = [
    // ...
    \Zipzoft\HttpLogger\LogRequestMiddleware::class,
];
```

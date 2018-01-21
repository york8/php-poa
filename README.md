# POA

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Total Downloads][ico-downloads]][link-downloads]

POA（Php cOroutine based Application framework）Web框架，灵感来自于 Node.js 的 KOA 框架，基于 React-PHP，
使用 PHP 的生成器来实现中间件，轻松的组合管理多个中间件之间的协作。

## 作者

- [York](https://github.com/york8)

## 安装
```bash
composer require york8/poa
```

## 使用
```php
// 1. create the Application
$app = new Application();

// 2. define the route rules
$router = new RouterMiddleware(function (Context $context) {
    $context->statusCode(404)->send('Not Found');
});
$router->get(
    '/foo/bar$',
    function (Context $context) {
        $context->send('Hello, ' . $context->getRequest()->getUri());
    }
)->get(
    '/foo/exception',
    function () {
        throw new Exception('I throw an exception just for fun, haha!');
    }
);

// 3. use middlewares what you need
$app->use(new ProfileMiddleware())
    ->use(function (Context $context) {
        $uri = $context->getRequest()->getUri();
        if ($uri->getPath() === '/bar') {
            $context->send('you fire.');
            return false;
        }
        return null;
    })
    ->use($router)
    ->useErrorMiddleware(function (Throwable $throwable, Context $context) {
        // 错误/异常 处理中间件
        $msg = $throwable->getMessage();
        fwrite(STDERR, $msg . "\n");
        $context->statusCode(500)->send('Oh, No! ' . $msg);
    });

// 4. listen and start the server
$app->listen(8088);
```

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/york8/poa.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/york8/poa.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/york8/poa
[link-downloads]: https://packagist.org/packages/york8/poa

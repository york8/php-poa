# POA

[![Latest Stable Version](https://poser.pugx.org/york8/poa/v/stable)](https://packagist.org/packages/york8/poa) 
[![Total Downloads](https://poser.pugx.org/york8/poa/downloads)](https://packagist.org/packages/york8/poa) 
[![Latest Unstable Version](https://poser.pugx.org/york8/poa/v/unstable)](https://packagist.org/packages/york8/poa) 
[![License](https://poser.pugx.org/york8/poa/license)](https://packagist.org/packages/york8/poa)

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
    $response = $context->getResponse()->withStatus(404);
    $response->getBody()->write('Not Found!');
    $context->setResponse($response);
});
$router->get(
    '/test$',
    function (Context $context) {
        $context->getResponse()->getBody()->write('Hello, test');
    }
);

// 3. use middlewares what you need
$app->use(new ProfileMiddleware())->use($router);

// 4. listen and start the server
$app->listen(8088);
```
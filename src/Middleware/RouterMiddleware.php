<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-07-10 14:14
 */

namespace York8\POA\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use York8\POA\Context;
use York8\Router\Router;

/**
 * Class RouterMiddleware
 * <p>路由器中间件，中间件子系统，本身也可以使用其它中间件
 * @package York8\POA\Middleware
 */
class RouterMiddleware extends Router implements MiddlewareInterface
{
    use MiddlewaresTrait;

    function handle(Context $context)
    {
        $attrs = [];
        $request = $context->getRequest();
        $handler = $this->route($request, $attrs);
        if (!empty($attrs)) {
            foreach ($attrs as $n => $v) {
                $request = $request->withAttribute($n, $v);
            }
            if ($request instanceof ServerRequestInterface) {
                $context->setRequest($request);
            }
        }

        yield;

        $handler($context);
    }
}

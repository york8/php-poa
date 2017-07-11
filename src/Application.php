<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-06-19 16:03
 */

namespace York8\POA;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application
{
    /** @var array APP 直接使用的中间件集合 */
    private $middlewares = [];

    function useMiddleware(callable $gen)
    {
        if (isMiddleware($gen)) {
            $this->middlewares[] = $gen;
        }
        return $this;
    }

    function run(ServerRequestInterface $request, ResponseInterface $response)
    {
        $gen = compose($this->middlewares);
        co($gen(null, $request, $response));
    }
}

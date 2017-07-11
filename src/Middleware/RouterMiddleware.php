<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-07-10 14:14
 */

namespace York8\POA\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use York8\Router\Router;

class RouterMiddleware extends Router implements MiddlewareInterface
{
    use MiddlewareTrait;

    /** {@inheritdoc} */
    public function handle($next, ServerRequestInterface $request, ResponseInterface $response)
    {
        $attrs = [];
        $handler = $this->route($request, $attrs);
        if (!$handler) {
            return;
        }

        yield $next;

        if (!empty($attrs)) {
            foreach ($attrs as $n => $v) {
                $request = $request->withAttribute($n, $v);
            }
        }
        $response = $handler->handle($request, $response);
        http_response_code($response->getStatusCode());
        $body = $response->getBody();
        $body->rewind();
        echo $body->getContents();
    }
}

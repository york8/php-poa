<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-07-10 16:53
 */

namespace York8\POA\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileMiddleware implements MiddlewareInterface
{
    use MiddlewareTrait;

    /** {@inheritdoc} */
    public function handle($next, ServerRequestInterface $request, ResponseInterface $response)
    {
        $start = microtime(true) * 1000;
        yield $next;
        $end = microtime(true) * 1000;
        echo "<br/>use time: ", $end - $start, 'ms';
    }
}

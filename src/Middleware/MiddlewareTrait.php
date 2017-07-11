<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-07-10 14:24
 */

namespace York8\POA\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

trait MiddlewareTrait
{
    /**
     * @param $next
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function __invoke($next, ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->handle($next, $request, $response);
    }
}
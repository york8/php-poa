<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-07-10 14:15
 */

namespace York8\POA\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use York8\POA\Context;

/**
 * 中间件接口
 * @package Middleware
 */
interface MiddlewareInterface
{
    /**
     * @param Context $context 请求上下文
     */
    public function __invoke(Context $context);
}

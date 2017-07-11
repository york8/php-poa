<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-07-10 14:15
 */

namespace York8\POA\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 中间件接口
 * @package Middleware
 */
interface MiddlewareInterface
{
    /**
     * 中间件函数，必须是生成器函数
     * @param mixed $next 后续处理
     * @param ServerRequestInterface $request 请求参数
     * @param ResponseInterface $response 响应参数
     */
    public function handle($next, ServerRequestInterface $request, ResponseInterface $response);

    /**
     * 调用 handle
     * @param mixed $next 后续处理
     * @param ServerRequestInterface $request 请求参数
     * @param ResponseInterface $response 响应参数
     * @see MiddlewareInterface::handle
     */
    public function __invoke($next, ServerRequestInterface $request, ResponseInterface $response);
}

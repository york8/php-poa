<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-06-20 16:50
 */

namespace York8\POA;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use York8\Router\HandlerInterface;

/**
 * 请求处理过程的上下文对象，中间件接收的输入参数，里面包含了请求、响应对象及一些有用的方法
 * @package York8\POA
 */
class Context
{
    /** @var ServerRequestInterface 请求对象 */
    private $request;

    /** @var ResponseInterface 响应对象 */
    private $response;

    /** @var HandlerInterface 处理器对象 */
    private $handler;

    private $rspCookies = [];

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return HandlerInterface
     */
    public function getHandler(): HandlerInterface
    {
        return $this->handler;
    }

    /**
     * @param HandlerInterface $handler
     */
    public function setHandler(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * 获取请求头
     * @param $name
     * @param null $defaultValue
     * @return string|null
     */
    public function getHeader($name, $defaultValue = null)
    {
        return $this->request->getHeader($name) ?: $defaultValue;
    }

    /**
     * 设置响应头
     * @param string $name
     * @param string $value
     * @return static
     */
    public function setHeader($name, $value)
    {
        $this->response = $this->response->withHeader($name, $value);
        return $this;
    }

    /**
     * 获取请求 cookie
     * @param string $name
     * @param string $defaultValue
     * @return string
     */
    public function getCookie($name, $defaultValue = null)
    {
        return $this->request->getCookieParams()[$name] ?: $defaultValue;
    }

    public function setCookie($name, $value)
    {
        if (is_null($value) || $value === '') {
            unset($this->rspCookies[$name]);
        } else {
            $this->rspCookies[$name] = $value;
        }
        return $this;
    }
}

<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-06-20 16:50
 */

namespace York8\POA;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

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

    /**
     * @var mixed[] 上下文相关的属性参数
     */
    private $attributes = [];

    use LoggerTrait;

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
     * 获取请求头或设置响应头
     * @param string $name 头部名称
     * @param null $value 值，null 表示删除
     * @return Context|\string[]
     */
    public function header($name, $value = null)
    {
        if (func_num_args() < 2) {
            return $this->request->getHeader($name);
        }
        if (is_null($value)) {
            $this->response->withoutHeader($name);
        } else {
            $this->setHeader($name, $value);
        }
        return $this;
    }

    /**
     * 获取请求所有查询参数
     * @return array
     */
    public function getQueryParams()
    {
        return $this->request->getQueryParams();
    }

    /**
     * 获取指定请求查询参数
     * @param $name
     * @return mixed
     */
    public function getQueryParam($name)
    {
        return @$this->getQueryParams()[$name];
    }

    /**
     * 获取请求体参数
     * @return array|null|object
     */
    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }

    /**
     * 获取或设置响应码，不传递任何参数表示获取当前的响应码
     * @param null $code 待设置的响应码
     * @param null $reasonPhrase 响应码说明
     * @return Context|int
     */
    public function statusCode($code = null, $reasonPhrase = null)
    {
        if (func_num_args() < 1 || empty($code)) {
            return $this->response->getStatusCode();
        }
        $this->response = $this->response->withStatus($code, $reasonPhrase);
        return $this;
    }

    /**
     * 清空响应输出流
     * @return $this
     */
    public function clear()
    {
        $rsp = $this->response;
        $body = $rsp->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
            $body->write("\0\0");
            $body->rewind();
        }
        return $this;
    }

    /**
     * 发送响应内容
     * @param string|StreamInterface $content 内容
     * @return $this
     */
    public function send($content)
    {
        if (is_string($content)) {
            $this->clear();
            $this->response->getBody()->write($content);
        } else if ($content instanceof StreamInterface) {
            $this->response = $this->response->withBody($content);
        }

        return $this;
    }

    /**
     * 获取或设置上下文属性参数
     * @param string $name 属性名称
     * @param mixed $value 属性值
     * @return $this|mixed
     */
    public function attr($name, $value = null)
    {
        if (func_num_args() < 2) {
            return @$this->attributes[$name];
        } else {
            if (is_null($value)) {
                unset($this->attributes[$name]);
            } else {
                $this->attributes[$name] = $value;
            }
            return $this;
        }
    }

    /**
     * @return \mixed[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param \mixed[] $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
}

<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-06-19 16:03
 */

namespace York8\POA;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Http\HttpBodyStream;
use React\Http\Response;
use React\Http\Server;
use React\Promise\Promise;

class Application
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var callable[] 请求处理中间件集合，在接收到一个请求连接时触发
     */
    private $middlewares = [];

    /**
     * @var callable[] 错误处理相关的中间件集合，在 错误/异常 发生时触发
     */
    private $errorMiddlewares = [];

    use LoggerTrait;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
        $this->loop = Factory::create();
        set_error_handler([$this, 'onError'], E_ALL);
        set_exception_handler([$this, 'onException']);
    }

    /**
     * 监听并启动程序
     * @param string $uri
     * @param array $context
     */
    public function listen($uri, array $context = [])
    {
        $server = new Server([$this, 'callback']);
        $socket = new \React\Socket\Server($uri, $this->loop, $context);
        $server->listen($socket);
        $this->loop->run();
    }

    /**
     * 请求处理回调函数
     * @param ServerRequestInterface $request
     * @return ResponseInterface|Promise
     */
    public function callback(ServerRequestInterface $request)
    {
        $response = new Response(200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ], '');

        return new Promise(function ($resolve, $reject) use (&$request, &$response) {
            try {
                $context = new Context($request, $response);
                $context->setLogger($this->getLogger());

                /**
                 * @var HttpBodyStream $body
                 */
                $body = $request->getBody();
                $body->on('data', function ($data) use (&$request, &$context) {
                    if (!$data) return;
                    $method = $request->getMethod();
                    $contentType = $request->getHeader('Content-Type');
                    if (isset($contentType[0])
                        && (strcasecmp($method, 'POST') === 0 || strcasecmp($method, 'PUT') === 0)
                    ) {
                        if (strpos(strtolower($contentType[0]), 'application/x-www-form-urlencoded') !== false) {
                            parse_str($data, $parsedBody);
                            $request = $request->withParsedBody($parsedBody);
                            $context->setRequest($request);
                        } else if (strpos(strtolower($contentType[0]), 'application/json') !== false) {
                            $parsedBody = json_decode($data, JSON_OBJECT_AS_ARRAY);
                            $request = $request->withParsedBody($parsedBody);
                            $context->setRequest($request);
                        }
                    }
                    $context->setAttribut('httpRawData', $data);
                });
                $body->on('end', function () use (&$context, $resolve) {
                    co(...$this->middlewares)($context);
                    $resolve($context->getReturn());
                });
                $body->on('error', function ($reason) use ($reject) {
                    $reject($reason);
                });
            } catch (\Throwable $exception) {
                // 触发错误异常处理中间件
                $this->onException($exception, $context);
                $rsp = $context->getResponse();
                if ($rsp === $response) {
                    $rsp = $response->withStatus(500);
                    $response->getBody()->write('Internal Server Error');
                }
                $resolve($rsp);
            }
        });
    }

    /**
     * （注册）使用请求处理中间件，中间件入参是上下文对象 Context。
     * @param callable $middleware
     * @return $this
     */
    public function use (callable $middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * （注册）使用全局错误处理中间件，中间件入参是异常对象 Throwable，以及一个可选的上下文对象 Context
     * <p>这里的错误处理中间件用来处理PHP程序发出的错误报告（errop_reporting）和全局未被捕获处理的异常；
     * <p>对于连接请求处理过程中的异常处理可以通过请求处生成器中间件来捕获处理，如下
     * <p>try { yield; } catch(...) { ... }
     * @param callable $middleware
     * @return $this
     */
    public function useErrorMiddleware(callable $middleware)
    {
        $this->errorMiddlewares[] = $middleware;
        return $this;
    }

    public function onError($errno, $errstr, $errfile, $errline)
    {
        if (!$errno || !($errno & error_reporting())) {
            return false;
        }
        $this->onException(new \ErrorException($errstr, 0, $errno, $errfile, $errline));
        return true;
    }

    public function onException(\Throwable $exception, Context $context = null)
    {
        try {
            $this->logger->warning($exception->getMessage(), ['exception' => $exception]);
            co(...$this->errorMiddlewares)($exception, $context);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }
}

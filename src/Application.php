<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-06-19 16:03
 */

namespace York8\POA;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Http\Server;

class Application
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var callable[]
     */
    private $middlewares = [];

    use LoggerTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
        $this->loop = Factory::create();
    }

    public function listen($uri, array $context = [])
    {
        $server = new Server([$this, 'callback']);
        $socket = new \React\Socket\Server($uri, $this->loop, $context);
        $server->listen($socket);
        $this->loop->run();
    }

    public function callback(ServerRequestInterface $request)
    {
        $response = new Response(200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ], '');

        try {
            $context = new Context($request, $response);
            $context->setLogger($this->getLogger());
            co(...$this->middlewares)($context);
            return $context->getResponse();
        } catch (\Throwable $exception) {
            $this->logger->warning($exception->getMessage(), ['exception' => $exception]);
            $response = $response->withStatus(500);
            $response->getBody()->write('Internal Server Error');
            return $response;
        }
    }

    public function use (callable $middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }
}

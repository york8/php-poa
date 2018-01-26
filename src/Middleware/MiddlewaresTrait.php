<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2018-01-21 0:13
 */

namespace York8\POA\Middleware;

use York8\POA\Context;
use function York8\POA\co;

/**
 * Trait MiddlewaresTrait
 * <p>多个中间件组成一个中间件子系统
 * @package York8\POA\Middleware
 */
Trait MiddlewaresTrait
{
    private $middlewares = [];

    public function use (callable $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function __invoke(Context $context)
    {
        return $this->run($context);
    }

    public function handle()
    {
    }

    private function run(...$params)
    {
        $middlewares = $this->middlewares;
        $middlewares[] = [$this, 'handle'];
        return co(...$middlewares)(...$params);
    }
}

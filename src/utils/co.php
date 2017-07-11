<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-06-19 16:41
 */

namespace York8\POA;

use Traversable;
use TypeError;

/**
 * 执行生成器
 * @param Traversable|array $it
 * @return mixed|null
 * @throws TypeError
 */
function co($it)
{
    if (!is_array($it) && !($it instanceof Traversable)) {
        throw new TypeError("Not Traversable");
    }
    $ret = NULL;
    foreach ($it as $o) {
        if ($o instanceof \Generator) {
            co($o);
        } else {
            $ret = $o;
        }
    }
    if ($it instanceof \Generator) {
        $ret = $it->getReturn();
    }
    return $ret;
}

/**
 * 将中间件组合成 Generator 函数
 * @param callable[] $middlewares
 * @return callable
 */
function compose($middlewares)
{
    return function ($next, ...$rest) use ($middlewares) {
        $i = count($middlewares);
        while ($i--) {
            $next = call_user_func($middlewares[$i], $next, ...$rest);
        }
        return $next;
    };
}

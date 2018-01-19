<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-06-19 16:41
 */

namespace York8\POA;

/**
 * 包装执行中间件（或 callable 对象集合）返回的一个闭包函数用于执行
 * @param array $middlewares
 * @return \Closure
 */
function co(...$middlewares)
{
    return function (...$params) use ($middlewares) {
        $genStack = [];
        foreach ($middlewares as &$m) {
            if (is_callable($m)) {
                $r = call_user_func_array($m, $params);
                if ($r instanceof \Generator) {
                    $r->current();
                    $genStack[] = $r;
                }
            } else if ($m instanceof \Generator) {
                $m->current();
                $genStack[] = $m;
            } else if (is_array($m) || $m instanceof \Traversable) {
                co(...$m)(...$params);
            }
        }

        while (($l = count($genStack)) > 0) {
            /**
             * @var \Generator $g
             */
            $g = array_pop($genStack);
            if ($g->valid()) {
                $g->next();
                array_unshift($genStack, $g);
            } else {
                unset($g);
            }
        }
    };
}

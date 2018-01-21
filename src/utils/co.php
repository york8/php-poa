<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-06-19 16:41
 */

namespace York8\POA;

/**
 * 包装执行中间件（或 callable 对象集合）返回的一个闭包函数用于执行。
 * <p>中间件必须是可被调用的（callable），或者是生成器对象（或函数）；
 * <p>中间件不应该有返回值，如果需要在多个中间件之间传递数据，请通过中间件的入参来进行；
 * <p>中间件的返回值有特殊含义，false 表示中止后续中间件的执行；
 * <p>默认所有的中间件都会被执行，可以通过返回 false 来提前中止后续中间件的调用。
 * <p>在生成器中间件的洋葱圈模型中，只能在进入的第一圈直接返回 false 来中止后续中间件的执行；
 * <p>co 会确保所有已执行的生成器中间件的洋葱圈流程完全执行完毕；
 * <p>入参也可以是一个数组或迭代器，其中的每一个成员都必须是一个中间件，这是中间件组合成的中间件子系统；
 * <p>子系统里面的所有中间件流程只有全部执行完毕后才会进入到下一个中间件的执行。
 * @param array $middlewares
 * @return \Closure
 */
function co(...$middlewares)
{
    return function (...$params) use ($middlewares) {
        $discontinue = null; // 是否中止
        $genStack = [];
        $exception = null;

        try {
            while (count($middlewares) > 0) {
                $m = array_shift($middlewares);
                if ($m instanceof \Generator) {
                    // 生成器中间件实现了洋葱圈模型
                    $ret = $m->current();
                    if ($m->valid()) {
                        // 生成器中间件入栈
                        $genStack[] = $m;
                        if ($ret === false) {
                            // 生成器中间件返回 false，提前中止后续中间件执行
                            // 不能直接 return，需要确保已经入栈的生成器执行收尾操作
                            $discontinue = true;
                            break;
                        }
                    } else {
                        // 生成器中间件已执行完毕，不需要入栈
                        if ($m->getReturn() === false) {
                            // 生成器中间件返回 false，提前中止后续中间件执行
                            // 不能直接 return，需要确保已经入栈的生成器执行收尾操作
                            $discontinue = true;
                            break;
                        }
                    }
                } else if (is_array($m) || $m instanceof \Traversable) {
                    // 中间件构成的子系统，只有里面的所有逻辑执行完后才会进入下一个中间件
                    if (co(...$m)(...$params) === false) {
                        // 子系统返回 false 提前中止
                        // 不能直接 return，需要确保已经入栈的生成器执行收尾操作
                        $discontinue = true;
                        break;
                    }
                } else if (is_callable($m)) {
                    $r = call_user_func_array($m, $params);
                    if ($r instanceof \Generator) {
                        // 插入生成器中间件
                        array_unshift($middlewares, $r);
                    } else if ($r === false) {
                        // 这是一个普通函数调用并且显示返回了 false，结束中间件的执行
                        // 不能直接 return，需要确保已经入栈的生成器执行收尾操作
                        $discontinue = true;
                        break;
                    }
                }
            }
        } catch (\Throwable $throwable) {
            // 中间件执行发生异常，不能中断已入栈的生成器中间件的收尾操作
            // 先把异常暂存下来
            $exception = $throwable;
        }

        // 确保已经入栈的生成器中间件可以完全执行完毕

        while (count($genStack) > 0) {
            /**
             * @var \Generator $g
             */
            $g = array_pop($genStack);
            if ($g->valid()) {
                try {
                    if ($exception) {
                        // 将之前捕获的异常抛进去，方便异常处理器进行处理
                        $g->throw($exception);
                        $exception = null;
                    } else {
                        $g->next();
                    }
                } catch (\Throwable $throwable) {
                    $exception = $throwable;
                }
                array_unshift($genStack, $g);
            } else {
                unset($g);
            }
        }

        if ($exception) {
            // 重新抛出之前暂存的异常
            throw $exception;
        }
        return $discontinue ? false : null;
    };
}

<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-07-10 16:53
 */

namespace York8\POA\Middleware;

use York8\POA\Context;

class ProfileMiddleware implements MiddlewareInterface
{
    /** {@inheritdoc} */
    public function __invoke(Context $context)
    {
        $start = microtime(true) * 1000;
        yield;
        $end = microtime(true) * 1000;
        echo "Profile: use time: ", $end - $start, "ms\n";
    }
}

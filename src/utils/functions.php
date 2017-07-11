<?php
/**
 * User: York <lianyupeng1988@126.com>
 * Date: 2017-06-20 16:36
 */

namespace York8\POA;

function isMiddleware($ware)
{
    return is_callable($ware) || $ware instanceof \Generator;
}

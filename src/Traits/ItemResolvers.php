<?php
namespace Haxibiao\Task\Traits;

use Haxibiao\Task\Item;

trait ItemResolvers
{

    public static function resolveFreeRandomItem($root, $args, $context, $info)
    {
        $user = getUser();
        $num  = 1;
        return Item::freeRandomItem($user, $num);
    }

    public static function resolveDailyFreeItem($root, $args, $context, $info)
    {
        $user = getUser();
        return Item::dailyFreeItem($user, $args['alias']);
    }
}

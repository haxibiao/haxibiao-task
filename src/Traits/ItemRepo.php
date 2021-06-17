<?php

namespace Haxibiao\Task\Traits;

use App\User;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Question\Helpers\Redis\RedisRewardCounter;
use Haxibiao\Task\Item;
use Illuminate\Support\Arr;

trait ItemRepo
{

    public static function freeRandomItem(User $user, $count = 1)
    {
        $item      = null;
        $freeItems = Item::free()->get()->toArray();
        $freeItem  = Arr::random($freeItems, $count);
        $freeItem  = end($freeItem);

        if (!is_null($freeItem)) {
            $itemId = $freeItem['id'];
            $user->addItem($itemId);
        }

        return $freeItem;
    }

    public static function dailyFreeItem(User $user, $alias)
    {
        $item = Item::alias($alias)->first();

        $dailyItemRestrictions = [
            'QUESTION_TIPS' => ['maxCount' => 1],
            'NEXT_QUESTION' => ['maxCount' => 2],
        ];
        $count = RedisRewardCounter::getRewardCounter($item->alias, $user->id);
        throw_if($count >= Arr::get($dailyItemRestrictions, $item->alias . '.maxCount', 0), UserException::class, '领取失败,今日领取已上限!');

        $user->addItem($item->id);

        RedisRewardCounter::updateRewardCounter($item->alias, $user->id);

        return $item;

    }
}

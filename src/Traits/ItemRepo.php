<?php

namespace Haxibiao\Task\Traits;

use App\Gold;
use App\User;
use Haxibiao\Breeze\Exceptions\GQLException;
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

    //领取道具
    public static function receiveItem($user, $item_id)
    {
        if ($user) {

            $item = Item::find($item_id);
            if ($item) {
                if ($user->items()->find($item_id)) {
                    //TODO:已经存在该道具，数量应该叠加
                    return false;
                }

                if ($item->price > 0) {
                    if ($item->price > $user->gold) {
                        throw new GQLException("金币不足，购买失败！");
                    }
                    Gold::makeOutcome($user, $item->value, "购买道具扣除");
                }

                $user->items()->syncWithoutDetaching([
                    $item->id => [
                        "total" => 1,
                    ],
                ]);
                return true;
            }
            throw new GQLException("不存在该物品！");
        }
        return false;
    }

    //我的道具
    public static function myItems()
    {
        if ($user = currentUser()) {
            return $user->items()->get();
        }
    }
}

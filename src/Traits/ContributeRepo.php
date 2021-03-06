<?php

namespace Haxibiao\Task\Traits;

use App\User;
use Haxibiao\Task\Contribute;

trait ContributeRepo
{
	public static function rewardUserComment($user,$comment,$remark=null){
		$contribute = self::firstOrNew(
			[
				'user_id'          => $user->id,
				'remark'           => $remark,
				'contributed_id'   => $comment->id,
				'contributed_type' => 'comments',
			]
		);
		$contribute->amount = self::COMMENTED_AMOUNT;
		$contribute->recountUserContribute();
		$contribute->save();
		return $contribute;
	}

    public static function rewardUserAction($user, $amount)
    {
        $contribute = Contribute::create(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $user->id,
                'contributed_type' => 'users',
                'amount'           => $amount,
            ]
        );

        return $contribute;
    }

    public static function rewardSignIn($user, $signIn, $amount)
    {
        $contribute = Contribute::create(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $signIn->id,
                'contributed_type' => 'sign_ins',
                'amount'           => $amount,
            ]
        );

        return $contribute;
    }
    public static function rewardUserContribute($user_id, $id, $amount, $type, $remark)
    {
        $contribute = Contribute::create(
            [
                'user_id'          => $user_id,
                'contributed_id'   => $id,
                'contributed_type' => $type,
                'remark'           => $remark,
                'amount'           => $amount,
            ]
        );
        $contribute->recountUserContribute();
        return $contribute;
    }

    public static function getCountByType(string $type, User $user)
    {
        return Contribute::where([
            'contributed_type' => $type,
            'user_id'          => $user->id,
        ])->whereRaw("created_at  >= curdate()")->count();
    }

    public static function getToDayCountByTypeAndId(string $type, $id, User $user)
    {
        return Contribute::where([
            'contributed_type' => $type,
            'contributed_id'   => $id,
            'user_id'          => $user->id,
        ])->whereDate('created_at', today())->count();
    }

}

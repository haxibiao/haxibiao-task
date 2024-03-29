<?php

namespace Haxibiao\Task;

use Haxibiao\Breeze\UserProfile;
use Haxibiao\Task\Traits\ContributeAttrs;
use Haxibiao\Task\Traits\ContributeRepo;
use Haxibiao\Task\Traits\ContributeResolvers;
use Illuminate\Database\Eloquent\Model;

class Contribute extends Model
{

    use ContributeAttrs;
    use ContributeResolvers;
    use ContributeRepo;

    // 发布有奖问答奖励贡献点汇率
    const ISSUE_CONVERSION_RATE = 10;

    // 提现金额与贡献值的汇率
    const WITHDRAW_DATE = 60;

    // 采纳问答奖励贡献点
    const REWARD_RESOLUTION_AMOUNT = 1;

    // 发布视频动态奖励贡献点
    const REWARD_VIDEO_POST_AMOUNT = 2;

    // DRAW 广告 morph 值
    const DRAW_AD_CONTRIBUTED_ID   = 1;
    const DRAW_AD_CONTRIBUTED_TYPE = 'AD';

    // FEED 广告 morph 值
    const AD_FEED_CONTRIBUTED_ID   = 3;
    const AD_FEED_CONTRIBUTED_TYPE = 'AD_FEED';

    // 激励视频 广告 morph 值
    const REWARD_VIDEO_CONTRIBUTED_ID   = 2;
    const REWARD_VIDEO_CONTRIBUTED_TYPE = 'AD_VIDEO';

    // 点广告奖励值
    const AD_AMOUNT = 1;

    // 看激励视频奖励值
    const AD_VIDEO_AMOUNT = 2;

    // 点赞奖励值
    const LIKED_AMOUNT = 1;

    // 评论奖励值
    const COMMENTED_AMOUNT = 1;

    // 视频刷点击广告奖励贡献值
    const REWARD_DRAW_AMOUNT = 2;

    // 最大draw点击次数
    const MAX_DRAW_CLICK = 5;
    // 最大feed点击次数
    const MAX_FEED_CLICK = 5;

    const REWARD_GOLD = 10;

    //每元/提现贡献点
    const TODAY_WITHDRAW_CONTRIBUTE  = 60;
    const RANDOM_WITHDRAW_CONTRIBUTE = 36;

    protected $guarded = [];

    protected $cast = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function contributed()
    {
        return $this->morphTo();
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'contributed_id');
    }

    public static function rewardUserVideoPost($user, $article, $remark)
    {
        //发布视频动态奖励+贡献
        $contribute = self::firstOrNew(
            [
                'user_id'          => $user->id,
                'remark'           => $remark,
                'contributed_id'   => $article->id,
                'contributed_type' => 'articles',
            ]
        );
        $contribute->amount = self::REWARD_VIDEO_POST_AMOUNT;
        $contribute->recountUserContribute();
        $contribute->save();
        return $contribute;
    }

    public static function getAmount($gold)
    {
        return $gold / self::ISSUE_CONVERSION_RATE;
    }

    public static function rewardUserIssuePost($user, $issue, $amount, $remark)
    {
        $contribute = self::firstOrNew(
            [
                'user_id'          => $user->id,
                'remark'           => $remark,
                'contributed_id'   => $issue->id,
                'contributed_type' => 'issues',
            ]
        );

        $contribute->amount = $amount;
        $contribute->save();
        $contribute->recountUserContribute();

        return $contribute;
    }

    public static function rewardUserResolution($user, $resolution, $amount, $remark)
    {
        $contribute = self::firstOrNew(
            [
                'user_id'          => $user->id,
                'remark'           => $remark,
                'contributed_id'   => $resolution->id,
                'contributed_type' => 'resolutions',
            ]
        );
        $contribute->amount = $amount;
        $contribute->recountUserContribute();
        $contribute->save();
        return $contribute;
    }

    public function recountUserContribute()
    {
        $user = $this->user;
        UserProfile::where('user_id', $user->id)->increment('count_contributes', $this->amount);
    }

    public function getTimeAgoAttribute()
    {
        return time_ago($this->created_at);
    }

    public function getBalanceAttribute()
    {
        return Contribute::where('id', '<', $this->id)->where('user_id', $this->user_id)->sum('amount');
    }

    public static function rewardSignInAdditional($user, $amount = 10)
    {
        $contribute = self::create(
            [
                'user_id'          => $user->id,
                'contributed_id'   => 0,
                'amount'           => $amount,
                'contributed_type' => 'sign_ins_additional',
            ]
        );

        return $contribute;
    }

    public static function rewardSignInDoubleReward($user, $signIn, $amount = 10)
    {
        $contribute = self::firstOrNew(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $signIn->id,
                'contributed_type' => 'sign_ins_double_reward',
            ]
        );
        $contribute->amount = $amount;
        $contribute->save();

        return $contribute;
    }
}

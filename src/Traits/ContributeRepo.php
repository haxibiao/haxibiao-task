<?php

namespace Haxibiao\Task\Traits;

use App\Invitation;
use App\User;
use Exception;
use Haxibiao\Breeze\Notifications\ReportSucceedNotification;
use Haxibiao\Question\Question;
use Haxibiao\Task\Contribute;
use Haxibiao\Wallet\BanUser;
use Illuminate\Support\Facades\Redis;

trait ContributeRepo
{
    public static function rewardUserComment($user, $comment, $remark = null)
    {
        $contribute = self::firstOrNew(
            [
                'user_id'          => $user->id,
                'remark'           => $remark,
                'contributed_id'   => $comment->id,
                'contributed_type' => 'comments',
            ]
        );
        $contribute->amount = Contribute::COMMENTED_AMOUNT;
        $contribute->recountUserContribute();
        $contribute->save();
        return $contribute;
    }

    public static function recountUserContributes(User $user)
    {
        //统计日贡献
        if ($user->ticket_restore_at < today()) {
            $user->ticket            = $user->level->ticket_max;
            $user->ticket_restore_at = now();
        }
        $user->today_contributes = $user->contributes()
            ->where('created_at', '>=', today())
            ->sum('amount');
        //有扣除贡献的场景，数据库贡献最小为0
        if ($user->today_contributes < 0) {
            $user->today_contributes = 0;
        }
        $user->saveDataOnly();
        //统计总贡献
        $profile                    = $user->profile;
        $profile->total_contributes = $user->contributes()->sum('amount');
        if ($profile->total_contributes < 0) {
            $profile->total_contributes = 0;
        }
        $profile->save();
    }

    /**
     * 点了激励视频广告
     */
    public static function rewardUserClickAd($user, $amount = 6)
    {
        $userId = $user->id;
        //这里限制了激励视频奖励最大次数
        if (self::canGetReward($userId)) {
            $contribute = Contribute::create(
                [
                    'user_id'          => $userId,
                    'contributed_id'   => 0,
                    'contributed_type' => 'reward_videos', //标记是激励视频产生的贡献行为记录
                    'amount' => $amount,
                ]
            );

            return $contribute;
        }
    }

    public static function rewardReport($report)
    {
        $contribute = self::firstOrNew(
            [
                'user_id'          => $report->user_id,
                'contributed_id'   => $report->id,
                'contributed_type' => 'reports',
            ]
        );
        $contribute->amount = 2; //举报成功贡献+2
        $contribute->save();
        $user = $report->user;
        $user->notify(new ReportSucceedNotification($report));
    }

    public static function rewardUserAudit($user, $audit)
    {
        $contribute = self::firstOrNew(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $audit->id,
                'contributed_type' => 'audits',
            ]
        );
        $contribute->amount = 1;
        $contribute->save();

        return $contribute;
    }

    //通用贡献奖励方法
    public static function makeInCome($user, $model, $amount)
    {
        $contribute = self::firstOrNew(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $model->id,
                'contributed_type' => $model->getMorphClass(),
            ]
        );
        $contribute->amount = $amount;
        $contribute->save();

        return $contribute;
    }

    public static function whenRemoveComment($user, $comment)
    {
        $contribute = self::create(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $comment->id,
                'contributed_type' => 'comments',
                'amount'           => -1,
            ]
        );
        return $contribute;
    }

    public static function whenRemoveQuestion($user, $question)
    {
        self::create(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $question->id,
                'contributed_type' => 'questions',
                'amount'           => -10,
            ]
        );
    }

    public static function rewardUserQuestion($user, $question)
    {
        //出题成功奖励＋6贡献
        $contribute = self::firstOrNew(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $question->id,
                'contributed_type' => 'questions',
            ]
        );
        // JIRA:DZ-1630 区分新老用户贡献点
        $amount             = $user->withdraw_count >= 7 ? 4 : Question::CONTRIBUTE_REWARD;
        $contribute->amount = $amount;
        $contribute->save();

        return $contribute;
    }

    public static function rewardInviteUser($rewardUser, $user)
    {
        //邀请成功奖励＋60贡献
        $contribute = self::firstOrNew([
            'user_id'          => $rewardUser->id,
            'contributed_id'   => $user->id,
            'contributed_type' => 'users',
        ]);
        $contribute->amount = Invitation::CONTRIBUTES_REWARD;
        $contribute->save();

        return $contribute;
    }

    public static function systemRewardUser($user, $amount = 10)
    {
        $contribute = self::create(
            [
                'user_id'          => $user->id,
                'contributed_id'   => 0,
                'amount'           => $amount,
                'contributed_type' => 'system',
            ]
        );

        return $contribute;
    }

    /**
     * 激励视频奖励情况
     * @param $user
     * @param $amount 根据场景，是否点击，贡献奖励有变化
     */
    public static function rewardVideoPlay($user, $amount)
    {
        $userId = $user->id;
        //这里限制了激励视频奖励最大次数
        if (self::canGetReward($userId)) {
            $contribute = Contribute::create(
                [
                    'user_id'          => $userId,
                    'contributed_id'   => 0,
                    'contributed_type' => 'reward_videos', //标记是激励视频产生的贡献行为记录
                    'amount' => $amount,
                ]
            );

            return $contribute;
        }
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

    public static function rewardLike($user, $like, $amount = 1)
    {
        $contribute = self::firstOrNew(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $like->id,
                'contributed_type' => 'likes',
            ]
        );
        $contribute->amount = $amount;
        $contribute->save();

        return $contribute;
    }

    //任务的指派奖励贡献
    public static function rewardAssignmentContribute($user, $assignment, $amount)
    {
        $type = "assignments";
        if ($assignment->task_id === \App\Task::whereName('有趣小视频')->first()->id) {
            $type = "reward_videos";
            // 检查是否还可再次获得激励视频奖励
            if (!self::canGetReward($user->id)) {
                return;
            }
        }
        Contribute::create(
            [
                'user_id'          => $user->id,
                'contributed_id'   => $assignment->id,
                'contributed_type' => $type,
                'amount'           => $amount,
            ]
        );
    }

    public static function rewardClickDrawFeed($user, $amount)
    {
        $contribute = self::create(
            [
                'user_id'          => $user->id,
                'contributed_id'   => 0,
                'amount'           => $amount,
                'contributed_type' => 'click_draw_feed',
            ]
        );

        return $contribute;
    }

    public static function rewardClickFeed($user, $amount)
    {
        $contribute = self::create(
            [
                'user_id'          => $user->id,
                'contributed_id'   => 0,
                'amount'           => $amount,
                'contributed_type' => 'click_feed',
            ]
        );

        return $contribute;
    }

    public static function canGetReward($userId)
    {
        $canReward = true;
        $redis     = Redis::connection('cache');
        try {
            if ($redis->ping()) {
                $cacheKey = date('Ymd') . ':user:reward:video:count';
                $redis->hincrby($cacheKey, $userId, 1);
                $redis->expireat($cacheKey, now()->endOfDay()->timestamp);

                $rewardVideoCount = $redis->hget($cacheKey, $userId);
                //超过100次后,不再获得奖励
                $canReward = !($rewardVideoCount > 100);
            }
        } catch (Exception $ex) {
            //丢给sentry报告
            app('sentry')->captureException($ex);
        }

        return $canReward;
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

    /**
     *  记录是否异常用户 4s内连续获得两次贡献? 太费性能，直接检查today_contributes属性超过600(提现10元都够了)
     */
    public static function detectBadUser($contribute)
    {
        $user = $contribute->user;
        $date = today();

        if ($user->today_contributes > 600) {
            $reason = "异常日期: {$date->toDateString()}，日贡献超过600";
            BanUser::record($user, $reason, false);
        }

        if ($user->profile->today_reward_video_count > 100) {
            //今天被封过的话直接跳过不检查
            $item = BanUser::where('user_id', $user->id)->where('updated_at', '>=', today())->first();
            if (empty($item)) {
                $reason = "异常日期: {$date->toDateString()}，日激励视频次数超过100";
                BanUser::record($user, $reason);
            }
        }

        // //每次created 贡献记录的时候 获取上一条的
        // $pre_data = \App\Contribute::query()
        //     ->where('user_id', $user->id)
        //     ->whereDate('created_at', $date)
        //     ->latest('id')
        //     ->skip(1)
        //     ->first();

        // if ($pre_data) {
        //     //如果两次获得贡献相差4s
        //     $diffSecond = $pre_data->created_at->diffInSeconds($contribute->created_at);
        //     if ($diffSecond <= 3) {
        //         $reason = "异常日期: {$date->toDateString()}，两次获得贡献时间相差：{$diffSecond} 秒";
        //         BanUser::create([
        //             'user_id' => $contribute->user_id,
        //             'reason'  => $reason,
        //         ]);
        //     }
        // }
    }
}

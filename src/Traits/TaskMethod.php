<?php

namespace Haxibiao\Task\Traits;

use App\Category;
use App\CategoryUser;
use App\Spider;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait TaskMethod
{

    /**
     * 通用检查任务方法
     * 如果只是一般检查任务进度，推荐使用（每日任务）
     *
     * @return bool
     */
    public function checkCommom($user, $task, $assignment)
    {
        $count = $assignment->current_count;
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count,
        ];
    }

    /**
     * 检查直播观看人数任务
     *
     * @return bool
     */
    public function checkAudienceCount($user, $task, $assignment)
    {

        $count = $user->count_audiences;
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count,
        ];
    }

    /**
     * 检查作品点赞任务
     *
     * @return bool true:已完成；false:未完成
     */
    public function checkLikesCount($user, $task, $assignment)
    {
        //$count = $user->profile->count_likes;
        $count = $assignment->current_count;
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count,
        ];
    }

    /**
     * 检查喝水赚钱任务
     *
     * @return bool
     */
    public function checkDrinkWater($user, $task, $assignment)
    {
        $count = $assignment->current_count;
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count, //喝水任务唯一的区别是知道你喝了哪几杯...
        ];
    }

    /**
     * 检查睡觉赚钱
     *
     * @return bool
     */
    public function checkSleep($user, $task, $assignment)
    {
        $count = $assignment->current_count;
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count, //睡觉任务唯一的区别是知道你目前是醒来的还是睡着...
        ];
    }

    /**
     * 检查视频动态的发布任务
     *
     * @return bool
     */
    //FIXME:现在走的是复制抖音的逻辑,这个检查函数虽然进来了,但是并不会有什么结果。
    public function checkPublishVideo($user, $task, $assignment)
    {
        $count = $user->profile->count_articles;
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count,
        ];
    }

    /**
     * 检查用户头像
     *
     * @return bool
     */
    public function checkUserHasAvatar($user, $task, $assignment)
    {
        //TODO: 判断是否有头像的要重构，avatar=null表示还没上传过头像
        return [
            'status'        => !Str::contains($user->avatar, 'storage/avatar/avatar'),
            'current_count' => 0,
        ];
    }

    //检测用户是否更换过昵称
    public function checkUserIsUpdateName($user, $task, $assignment)
    {
        return [
            'status'        => $user->name != User::DEFAULT_USER_NAME,
            'current_count' => 0,
        ];
    }

    /**
     * 检查用户手机号绑定
     *
     * @return bool
     */
    public function checkUserHasPhone($user, $task, $assignment)
    {
        return [
            'status'        => $user->phone,
            'current_count' => 0,
        ];
    }

    /**
     * 检查用户性别和生日
     *
     * @return bool
     */
    public function checkUserGenderAndBirthday($user, $task, $assignment)
    {
        $profile = $user->profile;
        return [
            'status'        => isset($profile->gender) && $profile->birthday,
            'current_count' => 0,
        ];
    }

    /**
     * 检查应用商店好评
     *
     * @return bool
     */
    public function checkAppStoreComment($user, $task, $assignment)
    {
        //TODO: 目前简单，日后这里加逻辑
        //无需审核，1分钟后任务自动完成

        return array(
            [
                'status'        => $assignment->status,
                'current_count' => 0,
            ],

        );
    }

    /**
     * 检查激励视频数量
     *
     * @return bool
     */
    public function checkRewardVideo($user, $task, $assignment)
    {
        //FIXME: 这个工厂里任务是看10次才奖励？？

        //TODO: 需要重构下面的属性来支撑
        // $count = $user->profile->today_reward_video_count;
        $count = 0;
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count,
        ];
    }

    /**
     * 检查试完App任务
     *
     * @return bool
     */
    public function checkDemoApp($user, $task, $assignment)
    {
        return array(
            [
                'status'        => false,
                'current_count' => 0,
            ],
        );
    }

    //检查邀请任务
    public function checkInviteUser($user, $task, $assignment)
    {
        //     $count = $user->count_success_invitation;

        //     if ($task_reach = $count >= $task->max_count) {
        //         //达成，自动领取任务奖励
        //         $gold = $task->reward['gold'];
        //         Gold::makeIncome($user, $gold, '邀请用户奖励');
        //         //更新状态到已领取
        //         $assignment->status = Assignment::TASK_DONE;
        //         $assignment->save();
        //     }
        //     return [
        //         'status'        => $task_reach,
        //         'current_count' => $count,
        //     ];
        return [
            'status'        => false,
            'current_count' => 0,
        ];
    }

    //每日邀请任务
    public function checkInviteUserToday($user, $task, $assignment)
    {
        $count = $user->today_invited_users_count;
        return [
            'status'        => $count > 0,
            'current_count' => $count,
        ];
    }

    //检查新型肺炎防治答10题
    public function checkCategoryAnswerQuestion($user, $task, $assignment)
    {
        // 19 代表 Categories 表中的 ID，它属于'医学知识', 比如 答赚里：140 代表新型肺炎...
        $category_id = Arr::get($task->resolve, 'category_id', 1);
        $category    = Category::find($category_id);
        if (is_null($category)) {
            return [
                'status'        => false,
                'current_count' => 0,
            ];
        }
        $sum = CategoryUser::where('user_id', $user->id)
            ->where('category_id', $category_id)
            ->where('last_answer_at', '>=', today()) //要求是24小时内的了...
            ->sum('answers_count_today');

        return [
            'status'        => $sum >= $task->max_count,
            'current_count' => $sum,
        ];
    }

    // 检查今日看激励视频次数
    public function checkTodayWatchRewardVideoCount($user, $task, $assignment)
    {
        $today_count = $user->profile->today_reward_video_count ?? 0;
        $done        = $today_count >= $task->max_count;
        return [
            'status'        => $done,
            'current_count' => $today_count,
            'is_over'       => $done, //决定是否不奖励，直接结束
        ];
    }

    //今日比赛获胜次数
    public function checkTodayGameWinnersCount($user, $task, $assignment)
    {
        $current_count = $user->gameWinners()->whereDate('created_at', today())->count();
        $status        = $current_count >= $task->max_count;
        return [
            'status'        => $status,
            'current_count' => $current_count,
        ];
    }

    //今日浏览次数(尊重resolve JSON里的 visits_type)
    public function checkTodayVisitsCount($user, $task, $assignment)
    {
        $visits_type   = Arr::get($task->resolve, 'visits_type', 'videos');
        $current_count = $user->visits()->ofType($visits_type)->whereDate('created_at', today())->count();
        $status        = $current_count >= $task->max_count;
        return [
            'status'        => $status,
            'current_count' => $current_count,
        ];
    }

    //检查是否第一次提现
    public function checkFirstWithdraw($user, $task, $assignment)
    {
        $status = $user->wallet->total_withdraw_amount > 0;
        return
            [
                'status'        => $status,
                'current_count' => 0,
            ];
    }

    //检查用户每日答题数
    public function checkAnswerQuestionCount($user, $task, $assignment)
    {
        $current_count = $user->answers()->whereDate('created_at', today())->count();
        $status        = $current_count >= $task->max_count;
        return
            [
                'status'        => $status,
                'current_count' => $current_count,
            ];
    }

    //检查用户是否更换过性别
    public function checkUserIsUpdateGender($user, $task, $assignment)
    {
        return [
            'status'        => $user->gender !== null,
            'current_count' => 0,
        ];
    }

    //检查用户是否填写过年龄
    public function checkAgeIsUpdate($user, $task, $assignment)
    {

        $status  = false;
        $profile = $user->profile()->select('age')->first();
        if (!is_null($profile)) {
            $status = $profile->age > 0;
        }
        return [
            'status'        => $status,
            'current_count' => 0,
        ];
    }

    //答赚 - 是否修改头像
    public function checkUserIsUpdateAvatar($user, $task, $assignment)
    {

        return
            [
                'status'        => !empty($user->avatar),
                'current_count' => 0,
            ];
    }

    // 检查今日提现金额是否达标
    public function checkTodayWithdrawAmount($user, $task, $assignment)
    {
        $resolve = $task->resolve;
        $amount  = Arr::get($resolve, 'amount');
        return [
            'status'        => is_numeric($amount) && $user->today_withdraw_amount >= $amount,
            'current_count' => 0,
        ];
    }

    // 检测天天答题任务
    public function checkDaliyAnswer($user, $task, &$assignment)
    {

        $todayAnswerCount = $user->profile->answers_count_today;
        $answerReward     = [
            ['answers_count' => 1, 'ticket' => 1],
            ['answers_count' => 50, 'ticket' => 10],
            ['answers_count' => 100, 'ticket' => 20],
            ['answers_count' => 300, 'ticket' => 40],
        ];

        $currentCount = 0;
        $rewardTicket = 0;
        foreach ($answerReward as $item) {
            if ($todayAnswerCount >= $item['answers_count']) {
                $rewardTicket += $item['ticket'];
                $currentCount++;
            }
        }

        $resolve                  = $assignment->resolve;
        $receiveTikect            = Arr::get($resolve, 'receive_ticket', 0);
        $resolve['reward_ticket'] = $rewardTicket;
        $assignment->resolve      = $resolve;
        return [
            'status'        => $currentCount > 0 && $rewardTicket > $receiveTikect,
            'current_count' => $currentCount,
        ];
    }

    public function checkUserProfile($user, $task, $assignment)
    {
        $isUpdatedAge    = Arr::get($this->checkAgeIsUpdate($user, $task, $assignment), 'status', false);
        // $isUpdatedAvatar = Arr::get($this->checkUserIsUpdateAvatar($user, $task, $assignment), 'status', false);
        $isUpdatedName   = Arr::get($this->checkUserIsUpdateName($user, $task, $assignment), 'status', false);
        $isUpdatedGender = Arr::get($this->checkUserIsUpdateGender($user, $task, $assignment), 'status', false);
        $isComplete      = $isUpdatedAge && $isUpdatedGender && $isUpdatedName;

        return [
            'status'        => $isComplete,
            'current_count' => (int) $isComplete,
        ];
    }

    public function checkTikTokPaste($user, $task, $assignment)
    {
        //获得当前热门标签
        $hot   = $task->resolve['hot'] ?? null;
        $count = 0;
        //当前用户粘贴的视频列表
        $spiders = \App\Spider::query()
            ->select(['user_id', 'data']) //不需要其他无用字段
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->where('status', 1)
            ->where('spider_type', 'videos')
            ->get();

        //匹配是否是热标签
        foreach ($spiders as $spider) {

            $title = $spider->data['title'] ?? null;
            if (!empty($title)) {
                $flag = strpos($title, $hot);
                if ($flag !== false) {
                    $count++;
                }
            }
        }
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count,
        ];
    }
}

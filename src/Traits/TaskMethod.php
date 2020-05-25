<?php

namespace haxibiao\task\Traits;

use App\Category;
use App\Contribute;
use Illuminate\Support\Str;

trait TaskMethod
{
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

    /**
     * 检查用户手机号绑定
     *
     * @return bool
     */
    public function checkUserHasPhone($user, $task, $assignment)
    {
        return [
            'status'        => $user->phone,
            'current_count' => null,
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
            'current_count' => null,
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
        //FIXME: getTodayCountByType表示获取今日激励视频奖励次数...
        $count = Contribute::getTodayCountByType(Contribute::REWARD_VIDEO_CONTRIBUTED_TYPE, $user);
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


    //检测用户是否更换过头像
    public function checkUserIsUpdateAvatar($user, $task, $assignment)
    {
        return [
            'status'        => !empty($user->avatar) && !strstr($user->avatar, 'default'),
            'current_count' => 0,
        ];
    }

    //检测用户答题数200道
    public function checkAnswerQuestionCount200($user, $task, $assignment)
    {
        return [
            'status'        => $user->profile()->select('answers_count_today')->first()->answers_count_today >= 200,
            'current_count' => 0
        ];
    }

    //检测用户答题数100道
    public function checkAnswerQuestionCount100($user, $task, $assignment)
    {
        return [
            'status'        => $user->profile()->select('answers_count_today')->first()->answers_count_today >= 100,
            'current_count' => 0
        ];
    }

    //检测用户答题数50道
    public function checkAnswerQuestionCount50($user, $task, $assignment)
    {
        return [
            'status'        => $user->profile()->select('answers_count_today')->first()->answers_count_today >= 50,
            'current_count' => 0
        ];
    }

    //检测用户答题数1道
    public function checkAnswerQuestionCount1($user, $task, $assignment)
    {
        return [
            'status'        => $user->profile()->select('answers_count_today')->first()->answers_count_today >= 1,
            'current_count' => 0
        ];
    }

    //检测用户是否更换过昵称
    public function checkUserIsUpdateName($user, $task, $assignment)
    {
        return [
            'status'        => $user->name != '匿名答友',
            'current_count' => 0
        ];
    }

    //检查用户是否设置性别
    public function checkUserIsUpdateGender($user, $task, $assignment)
    {
        return [
            'status'        => $user->gender !== null,
            'current_count' => 0
        ];
    }

    //检查用户是否填写过年龄
    public function checkAgeIsUpdate($user, $task, $assignment)
    {
        return [
            'status'        => $user->profile()->select('age')->first()->age > 0,
            'current_count' => 0
        ];
    }

    //检查新型肺炎防治答10题
    public function checkCategoryAnswerQuestion($user, $task, $assignment)
    {
        $category = Category::find(5);
        if (is_null($category)) {
            return [
                'status'        => false,
                'current_count' => 0
                ];
        }

        return [
            'status'        => CategoryUser::where('user_id', $user->id)
                ->where('category_id', 5)
                ->where('answers_count_today', '>=', 10)
                ->where('last_answer_at', '>=', today())
                ->exists(),
            'current_count' => 0
        ];
    }

    //检查今日比赛获胜5次
    public function checkTodayGameWinnersCount($user, $task, $assignment)
    {
        return [
            'status'        => $user->gameWinners()->today()->count() >= 5,
            'current_count' => 0
        ];
    }

    //检查观看学习视频50个
    public function checkTodayVisitsCount($user, $task, $assignment)
    {
        return [
            'status'        => $user->visits()->ofType('videos')->today()->count() >= 50,
            'current_count' => 0
        ];
    }
}

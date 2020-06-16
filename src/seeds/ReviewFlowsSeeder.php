<?php
namespace haxibiao\task;

use haxibiao\task\ReviewFlow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewFlowsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('review_flows')->truncate();
        $reviewFlows = [
            [
                'name'            => '试玩答妹',
                'check_functions' => null,
            ],
            [
                'name'            => '应用好评',
                'check_functions' => null,
            ],
            [
                'name'            => '在线出题',
                'check_functions' => null,
            ],
            [
                'name'            => '抖音采集',
                'check_functions' => null,
            ],
            [
                'name'            => '更换头像',
                'check_functions' => ['checkUserIsUpdateAvatar'],
                'review_class'    => 'User',
            ],
            [
                'name'            => '答题总数',
                'check_functions' => ['checkAnswerQuestionCount'],
                'review_class'    => 'Answer',
            ],
            [
                'name'            => '更换昵称',
                'check_functions' => ['checkUserIsUpdateName'],
                'review_class'    => 'User',
            ],
            [
                'name'            => '设置性别',
                'check_functions' => ['checkUserIsUpdateGender'],
                'review_class'    => 'User',
            ],
            [
                'name'            => '设置年龄',
                'check_functions' => ['checkAgeIsUpdate'],
                'review_class'    => 'Profile',
            ],
            [
                'name'            => '新冠答题数',
                'check_functions' => ['checkCategoryAnswerQuestion'],
                'review_class'    => 'Answer',
            ],
            [
                'name'            => '答题PK',
                'check_functions' => ['checkTodayGameWinnersCount'],
                'review_class'    => 'Game',
            ],
            [
                'name'            => '刷视频',
                'check_functions' => ['checkTodayVisitsCount'],
                'review_class'    => 'Visit',
            ],
            [
                'name'            => '新手答题',
                'check_functions' => ['checkAnswerQuestionCount'],
                'review_class'    => 'Answer',
            ],
            [
                'name'            => '首次提现奖励',
                'check_functions' => ['checkFirstWithdraw'],
                'review_class'    => 'Withdraw',
            ],
            [
                'name'            => '看激励视频',
                'check_functions' => ['checkTodayWatchRewardVideoCount'],
                'review_class'    => 'Gold',
            ],
            [
                'name'            => '喝水赚钱',
                'check_functions' => ['checkDrinkWater'],
            ],
            [
                'name'            => '睡觉赚钱',
                'check_functions' => ['checkSleep'],
            ],
            [
                'name'            => '视频发布',
                'check_functions' => ['checkPublishVideo'],
                'review_class'    => 'Post',
            ],
            [
                'name'            => '看视频赚钱',
                'check_functions' => ['checkRewardVideo'],
                'review_class'    => 'Gold',
            ],
            [
                'name'            => '完善头像',
                'check_functions' => ['checkUserHasAvatar'],
                'review_class'    => 'Profile',
            ],
            [
                'name'            => '绑定手机号',
                'check_functions' => ['checkUserHasPhone'],
                'review_class'    => 'User',
            ],
            [
                'name'            => '修改性别和生日',
                'check_functions' => ['checkUserGenderAndBirthday'],
                'review_class'    => 'Profile',
            ],
            [
                'name'            => '应用商店好评',
                'check_functions' => ['checkAppStoreComment'],
            ],
            [
                'name'            => '最大观众数量',
                'check_functions' => ['checkAudienceCount'],
                'review_class'    => 'UserLive',
            ],
            [
                'name'            => '点赞数量统计',
                'check_functions' => ['checkLikesCount'],
                'review_class'    => 'Like',
            ],
            [
                'name'            => '邀请用户统计',
                'check_functions' => ['checkInviteUser'],
                'review_class'    => 'Invite',
            ],
        ];
        foreach ($reviewFlows as $reviewFlow) {
            ReviewFlow::firstOrCreate($reviewFlow);
        }
    }
}

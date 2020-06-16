<?php
namespace haxibiao\task;

use App\ReviewFlow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DZReviewFlowsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //设置 检查任务模板，不可以在线上环境随便的调用该类
        DB::table('review_flows')->truncate();

        $reviewFlows = [
            [
                'name'                => '检测用户是否更换过头像',
                'check_functions'     => ['checkUserIsUpdateAvatar'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '检测用户答题数',
                'check_functions'     => ['checkAnswerQuestionCount'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '检测用户是否更换过昵称',
                'check_functions'     => ['checkUserIsUpdateName'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '检查用户是否更换过性别',
                'check_functions'     => ['checkUserIsUpdateGender'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '检查用户是否填写过年龄',
                'check_functions'     => ['checkAgeIsUpdate'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '检查用户分类答题数',
                'check_functions'     => ['checkCategoryAnswerQuestion'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '检查今日比赛获胜次数',
                'check_functions'     => ['checkTodayGameWinnersCount'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '今日浏览次数',
                'check_functions'     => ['checkTodayVisitsCount'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '新手答题',
                'check_functions'     => ['checkAnswerQuestionCount'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '首次提现奖励',
                'check_functions'     => ['checkFirstWithdraw'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
            [
                'name'                => '检查今日用户观看激励视频次数',
                'check_functions'     => ['checkTodayWatchRewardVideoCount'],
                'need_owner_review'   => false,
                'need_offical_review' => false,
                'type'                => 1, //1代表只能后台用户选用
            ],
        ];
        foreach ($reviewFlows as $reviewFlow) {
            ReviewFlow::firstOrCreate($reviewFlow);
        }
    }
}

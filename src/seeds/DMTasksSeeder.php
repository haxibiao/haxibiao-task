<?php

namespace Haxibiao\Task;

use App\ReviewFlow;
use App\Task;
use Haxibiao\Task\Assignment;
use Illuminate\Database\Seeder;

class DMTasksSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //需要清理
        // Assignment::truncate();
        //Task::truncate();

        $this->initNewUserTasks();
        $this->initDailyTasks();

        $this->initCustomTasks();
        $this->initContributeTasks();
        $this->initGroupTasks();
    }

    //这些以前是前端代码+rest API的，可以直接用新的数据库结构seed
    public function initContributeTasks()
    {
        //贡献任务（都是前端可以直接重复做的）
        $task = $this->saveTask([
            'name' => '有趣小视频',
            'type' => Task::CONTRIBUTE_TASK,
        ]);
        $task->reward = [
            'gold'            => 0,
            'ticket'          => 10,
            'contribute'      => 2,
            'gold_high'       => 10, //点击了详情走高额奖励...
            'ticket_high'     => 10,
            'contribute_high' => 6,
        ];
        $task->max_count      = 20;
        $task->review_flow_id = ReviewFlow::whereName('看激励视频')->first()->id;
        $task->status         = true;
        $task->group          = "贡献任务";
        $task->details = '看完视频即可获取奖励，点击下载广告或查看详情才能获取更多贡献点奖励哦~';
        $task->save();

        $task = $this->saveTask([
            'name' => '在线出题',
            'type' => Task::CONTRIBUTE_TASK,

        ]);
        $task->reward = [
            'gold'       => 10,
            'ticket'     => -1,
            'contribute' => 10,
        ];
        $task->review_flow_id = ReviewFlow::whereName('在线出题')->first()->id;
        $task->status         = true;

        $task->group = "贡献任务";
        $task->details = '每出1题消耗1精力点，只有成功通过审核的题目才能获得出题奖励。出图文视频题或添加详细的解析内容可以获得更多的奖励哦~';
        $task->save();

        $task = $this->saveTask([
            'name' => '采集抖音视频',
            'type' => Task::CONTRIBUTE_TASK,
        ]);
        $task->reward = [
            'gold'       => 10,
            'ticket'     => 0,
            'contribute' => 0,
        ];
        $task->review_flow_id = ReviewFlow::whereName('抖音采集')->first()->id;
        $task->status         = true;

        $task->group = "贡献任务";
        $task->details = '打开抖音视频-点击分享按钮-选择复制链接，返回答妹即可触发视频采集，采集成功将获得智慧点奖励，如果采集了热门标签视频，可以获得额外贡献点奖励哦~';
        $task->save();


        $task = $this->saveTask([
            'name' => '高额提现抽奖',
            'type' => Task::CONTRIBUTE_TASK,
        ]);
        $task->reward = [
            'gold'       => 0,
            'ticket'     => 0,
            'contribute' => 0,
        ];
        $task->review_flow_id = ReviewFlow::whereName('高额抽奖')->first()->id;
        $task->status         = true;

        $task->group = "贡献任务";
        $task->details = '1. 首次免费参与，其他情况下完成所有福利任务后，可以参与报名；2. 奖金一共分为4档【1.1，2，4，8】；3. 中奖后，需要在结果公布当日申请提现，否则名额作废；4. 系统通知会通知中奖用户具体的中奖信息，另外官方公告中会有当日所有中奖用户的名单。';
        $task->save();
    }

    //新人任务
    public function initNewUserTasks()
    {
        $task = $this->saveTask([
            'name' => '修改昵称',
            'type' => Task::NEW_USER_TASK,
        ]);
        $task->reward = [
            'gold'   => 5,
            'ticket' => 0,
        ];
        $task->review_flow_id = ReviewFlow::whereName('更换昵称')->first()->id;
        $task->status         = true;

        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name' => '上传新头像',
            'type' => Task::NEW_USER_TASK,

        ]);
        $task->reward = [
            'gold'   => 10,
            'ticket' => 0,
        ];
        $task->review_flow_id = ReviewFlow::whereName('更换头像')->first()->id;
        $task->status         = true;

        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name' => '完善性别',
            'type' => Task::NEW_USER_TASK,
        ]);
        $task->reward = [
            'gold'   => 10,
            'ticket' => 0,
        ];
        $task->review_flow_id = ReviewFlow::whereName('设置性别')->first()->id;
        $task->status         = true;

        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name' => '完善年龄',
            'type' => Task::NEW_USER_TASK,
        ]);
        $task->reward = [
            'gold'   => 5,
            'ticket' => 0,
        ];
        $task->review_flow_id = ReviewFlow::whereName('设置年龄')->first()->id;
        $task->status         = true;

        $task->group = "新人任务";
        $task->save();
    }

    //每日任务
    public function initDailyTasks()
    {
        $task = $this->saveTask([
            'name' => '每天答题100道',
            'type' => Task::DAILY_TASK,
        ]);
        $task->reward = [
            'gold'   => 0,
            'ticket' => 20,
        ];
        $task->max_count      = 100;
        $task->review_flow_id = ReviewFlow::whereName('答题总数')->first()->id;
        $task->status         = true;

        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name' => '每天答题50道',
            'type' => Task::DAILY_TASK,
        ]);
        $task->reward = [
            'gold'   => 0,
            'ticket' => 10,
        ];
        $task->max_count      = 50;
        $task->review_flow_id = ReviewFlow::whereName('答题总数')->first()->id;
        $task->status         = true;

        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name' => '每天答题1道',
            'type' => Task::DAILY_TASK,
        ]);
        $task->reward = [
            'gold'   => 0,
            'ticket' => 1,
        ];
        $task->max_count      = 1;
        $task->review_flow_id = ReviewFlow::whereName('答题总数')->first()->id;
        $task->status         = true;

        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name' => '观看学习视频50个',
            'type' => Task::DAILY_TASK,
        ]);
        $task->reward = [
            'gold'   => 10,
            'ticket' => 0,
        ];
        $task->max_count      = 50;
        $task->review_flow_id = ReviewFlow::whereName('刷视频')->first()->id;
        $task->status         = true;
        $task->resolve        = ["visits_type" => "posts", "route" => "学习"];

        $task->group = "每日任务";
        $task->save();


        $task = $this->saveTask([
            'name' => '每日邀请用户',
            'type' => Task::DAILY_TASK,
        ]);
        $task->reward = [
            'gold'   => 20,
            'ticket' => 0,
        ];
        $task->max_count      = 1;
        $task->review_flow_id = ReviewFlow::whereName('每日邀请用户统计')->first()->id;
        $task->status         = true;

        $task->group = "每日任务";
        $task->save();



        $task = $this->saveTask([
            'name' => '热门标签视频',
            'type' => Task::DAILY_TASK,
        ]);
        $task->reward = [
            'gold'   => 20,
            'contribute' => 1,
        ];
        $task->max_count      = 2;
        $task->review_flow_id = ReviewFlow::whereName('粘贴热门标签视频')->first()->id;
        $task->status         = true;
        $task->group = "每日任务";
        $task->save();
    }

    public function initCustomTasks()
    {
        //自定义任务
        $task = $this->saveTask([
            'name'    => '应用商店好评',
            'details' => "好评即可获得600智慧奖励！",
            'type'    => Task::CUSTOM_TASK,

        ]);
        $task->reward = [
            'gold'   => 600,
            'ticket' => 0,
        ];
        $task->review_flow_id = ReviewFlow::whereName('应用好评')->first()->id;
        $task->status         = true;
        $task->resolve        = ["router" => "SubmitTask", "submit_name" => "去评价"];

        $task->group = "自定义任务";
        $task->save();
    }

    public function initGroupTasks()
    {
        $task = $this->saveTask(
            [
                'name' => '每天答题越多,奖励越多!',
                'details' => '每天答题越多,奖励越多!',
                'type' => TASK::GROUP_TASK,
            ]
        );
        $task->reward = [
            [
                'answers_count' => 1,
                'ticket'        => 1,
            ],
            [
                'answers_count' => 50,
                'ticket'        => 10,
            ],
            [
                'answers_count' => 100,
                'ticket'        => 20,
            ],
            [
                'answers_count' => 300,
                'ticket'        => 40,
            ],

        ];
        $task->review_flow_id = ReviewFlow::whereName('每日答题任务(聚合)')->first()->id;
        $task->status         = 0;
        $task->max_count = 4;
        $task->resolve        = [
            'answers_count' => [1, 50, 100, 300],
        ];

        $task->group = "每日任务";
        $task->save();


        $task = $this->saveTask(
            [
                'name' => '完善个人资料',
                'details'        => '只有当您上传头像、完善昵称、填写年龄、填写性别资料后才可以领取任务奖励哦。',
                'type'           => Task::NEW_USER_TASK
            ]
        );

        $task->review_flow_id = ReviewFlow::whereName('完善个人资料')->first()->id;
        $task->status         = true;
        $task->max_count = 1;
        $task->reward = [
            'gold' => 20
        ];
        $task->group = "新人任务";
        $task->save();
    }

    public function saveTask(array $data)
    {
        return Task::firstOrCreate($data);
    }
}

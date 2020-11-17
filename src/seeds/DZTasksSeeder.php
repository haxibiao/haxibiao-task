<?php

namespace Haxibiao\Task\seeds;

use App\ReviewFlow;
use App\Task;
use Illuminate\Database\Seeder;

class DZTasksSeeder extends Seeder
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
        // Task::truncate();

        $this->initNewUserTasks();
        $this->initDailyTasks();
        $this->initCustomTasks();
        $this->initContributeTasks();

        //PK任务开关
        // if ($task = Task::where('name', '每天胜利5场PK')->first()) {
        //     // 每天胜利5场PK的任务...
        //     $task->status = false;
        //     $task->save();
        // }
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
        $task->icon           = "task/task16_1592445997.png";
        $task->group          = "贡献任务";
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
        $task->icon           = "task/task17_1592445960.png";

        $task->group = "贡献任务";
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
        $task->icon           = "task/task18_1592445846.png";

        $task->group = "贡献任务";
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
        $task->icon           = "task/task1_1592443870.png";

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
        $task->icon           = "task/task2_1592444819.png";

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
        $task->icon           = "task/task3_1592444878.png";

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
        $task->icon           = "task/task4_1592444929.png";

        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name'    => '新手答题',
            'type'    => Task::NEW_USER_TASK,
            'details' => '新人答10题可提现0.3元，秒到账',

        ]);
        $task->reward = [
            'gold'   => 10,
            'ticket' => 0,
        ];
        $task->max_count      = 10;
        $task->review_flow_id = ReviewFlow::whereName('新手答题')->first()->id;
        $task->status         = true;
        $task->icon           = "task/task5_1592444990.png";

        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name'    => '首次提现奖励',
            'type'    => Task::NEW_USER_TASK,
            'details' => '首次提现后可获得额外奖励，明日最高可提现10元',
        ]);
        $task->reward = [
            'gold'   => 20,
            'ticket' => 0,
        ];
        $task->review_flow_id = ReviewFlow::whereName('首次提现奖励')->first()->id;
        $task->status         = true;
        $task->icon           = "task/task6_1592446511.png";

        $task->group = "新人任务";
        $task->save();
    }

    //每日任务
    public function initDailyTasks()
    {
        $task = $this->saveTask([
            'name' => '每天答题200道',
            'type' => Task::DAILY_TASK,
        ]);
        $task->reward = [
            'gold'   => 0,
            'ticket' => 40,
        ];
        $task->max_count      = 200;
        $task->review_flow_id = ReviewFlow::whereName('答题总数')->first()->id;
        $task->status         = true;
        $task->icon           = "task/task5_1592444990.png";

        $task->group = "每日任务";
        $task->save();

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
        $task->icon           = "task/task5_1592444990.png";

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
        $task->icon           = "task/task5_1592444990.png";

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
        $task->icon           = "task/task5_1592444990.png";

        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name' => '新型肺炎防治答10题',
            'type' => Task::DAILY_TASK,
        ]);
        $task->reward = [
            'gold'   => 5,
            'ticket' => 5,
        ];
        $task->max_count      = 10;
        $task->review_flow_id = ReviewFlow::whereName('新冠答题数')->first()->id;
        $task->status         = true;
        $task->resolve        = ["category_id" => 140, "submit_name" => "去答题"];
        $task->icon           = "task/task5_1592444990.png";
        $task->group          = "每日任务";
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
            'name' => '每天胜利5场PK',
            'type' => Task::DAILY_TASK,
        ]);
        $task->reward = [
            'gold'   => 30,
            'ticket' => 0,
        ];
        $task->max_count      = 5;
        $task->review_flow_id = ReviewFlow::whereName('答题PK')->first()->id;
        $task->icon           = "task/task13_1592446392.png";
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
        $task->icon           = "task/task15_1592446299.png";
        $task->group          = "自定义任务";
        $task->save();

        $task = $this->saveTask([
            'name'    => '试玩同款APP(答妹)',
            'details' => "书中自有颜如玉，学知识领现金;点击“下载”按钮跳转至应用商店安装APP，并完成注册才能领取奖励。",
            'type'    => Task::CUSTOM_TASK,
        ]);
        $task->reward = [
            'gold'   => 50,
            'ticket' => 0,
        ];
        $task->review_flow_id = ReviewFlow::whereName('试玩答妹')->first()->id;
        $task->status         = true;
        $task->icon           = "task/task15_1592446299.png";
        $task->resolve        = ["package" => "com.damei", "post_id" => 15053];

        $task->group = "自定义任务";
        $task->save();
    }

    public function saveTask(array $data)
    {
        return Task::firstOrCreate($data);
    }
}

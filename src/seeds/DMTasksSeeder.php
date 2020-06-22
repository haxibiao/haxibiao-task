<?php

namespace haxibiao\task;

use App\ReviewFlow;
use App\Task;
use haxibiao\task\Assignment;
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
        Assignment::truncate();
        Task::truncate();

        $this->initNewUserTasks();
        $this->initDailyTasks();
        $this->initCustomTasks();
        $this->initContributeTasks();
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

    public function saveTask(array $data)
    {
        return Task::firstOrCreate($data);
    }
}

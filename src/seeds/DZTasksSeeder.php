<?php
namespace haxibiao\task;

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
        //旧版本需要清理的
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
            'name'           => '有趣小视频',
            'type'           => Task::CONTRIBUTE_TASK,
            'reward'         => [
                'gold'            => 0,
                'ticket'          => 10,
                'contribute'      => 2,
                'gold_high'       => 10, //点击了详情走高额奖励...
                'ticket_high'     => 10,
                'contribute_high' => 6,
            ],
            'max_count'      => 20,
            'review_flow_id' => ReviewFlow::whereName('检查今日用户观看激励视频次数')->first()->id, //这些都是前端直接做，前端知道结果，不需要后端审查
            'status'         => true,
        ]);
        $task->group = "贡献任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '在线出题',
            'type'           => Task::CONTRIBUTE_TASK,
            'reward'         => [
                'gold'       => 10,
                'ticket'     => -1,
                'contribute' => 10,
            ],
            'review_flow_id' => null, //这些都是前端直接做，前端知道结果，不需要后端审查
            'status'         => true,
        ]);
        $task->group = "贡献任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '采集抖音视频',
            'type'           => Task::CONTRIBUTE_TASK,
            'reward'         => [
                'gold'       => 10,
                'ticket'     => 0,
                'contribute' => 0,
            ],
            'review_flow_id' => null, //这些都是前端直接做，前端知道结果，不需要后端审查
            'status'         => true,
        ]);
        $task->group = "贡献任务";
        $task->save();

    }

    //新人任务
    public function initNewUserTasks()
    {
        $task = $this->saveTask([
            'name'           => '修改昵称',
            'type'           => Task::NEW_USER_TASK,
            'reward'         => [
                'gold'   => 5,
                'ticket' => 0,
            ],
            'review_flow_id' => ReviewFlow::whereName('检测用户是否更换过昵称')->first()->id,
            'status'         => true,
        ]);
        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '上传新头像',
            'type'           => Task::NEW_USER_TASK,
            'reward'         => [
                'gold'   => 10,
                'ticket' => 0,
            ],
            'review_flow_id' => ReviewFlow::whereName('检测用户是否更换过头像')->first()->id,
            'status'         => true,
        ]);
        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '完善性别',
            'type'           => Task::NEW_USER_TASK,
            'reward'         => [
                'gold'   => 10,
                'ticket' => 0,
            ],
            'review_flow_id' => ReviewFlow::whereName('检查用户是否更换过性别')->first()->id,
            'status'         => true,
        ]);
        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '完善年龄',
            'type'           => Task::NEW_USER_TASK,
            'reward'         => [
                'gold'   => 5,
                'ticket' => 0,
            ],
            'review_flow_id' => ReviewFlow::whereName('检查用户是否填写过年龄')->first()->id,
            'status'         => true,
        ]);
        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '新手答题',
            'details'        => '新人答10题可提现0.3元，秒到账',
            'type'           => Task::NEW_USER_TASK,
            'reward'         => [
                'gold'   => 10,
                'ticket' => 0,
            ],
            'review_flow_id' => ReviewFlow::whereName('新手答题')->first()->id,
            'status'         => true,
            'max_count'      => 10,
        ]);
        $task->group = "新人任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '首次提现奖励',
            'details'        => '首次提现后可获得额外奖励，明日最高可提现10元',
            'type'           => Task::NEW_USER_TASK,
            'reward'         => [
                'gold'   => 20,
                'ticket' => 0,
            ],
            'review_flow_id' => ReviewFlow::whereName('首次提现奖励')->first()->id,
            'status'         => true,
        ]);
        $task->group = "新人任务";
        $task->save();

    }

    //每日任务
    public function initDailyTasks()
    {
        $task = $this->saveTask([
            'name'           => '每天答题200道',
            'type'           => Task::DAILY_TASK,
            'reward'         => [
                'gold'   => 0,
                'ticket' => 40,
            ],
            'max_count'      => 200,
            'review_flow_id' => ReviewFlow::whereName('检测用户答题数')->first()->id,
            'status'         => true,
        ]);
        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '每天答题100道',
            'type'           => Task::DAILY_TASK,
            'reward'         => [
                'gold'   => 0,
                'ticket' => 20,
            ],
            'max_count'      => 100,
            'review_flow_id' => ReviewFlow::whereName('检测用户答题数')->first()->id,
            'status'         => true,
        ]);
        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '每天答题50道',
            'type'           => Task::DAILY_TASK,
            'reward'         => [
                'gold'   => 0,
                'ticket' => 10,
            ],
            'max_count'      => 50,
            'review_flow_id' => ReviewFlow::whereName('检测用户答题数')->first()->id,
            'status'         => true,
        ]);
        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '每天答题1道',
            'type'           => Task::DAILY_TASK,
            'reward'         => [
                'gold'   => 0,
                'ticket' => 1,
            ],
            'max_count'      => 1,
            'review_flow_id' => ReviewFlow::whereName('检测用户答题数')->first()->id,
            'status'         => true,
        ]);
        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '新型肺炎防治答10题',
            'type'           => Task::DAILY_TASK,
            'reward'         => [
                'gold'   => 5,
                'ticket' => 5,
            ],
            'max_count'      => 10,
            'review_flow_id' => ReviewFlow::whereName('检查用户分类答题数')->first()->id,
            'status'         => true,
            'resolve'        => ["category_id" => 140, "submit_name" => "去答题"],
        ]);
        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '观看学习视频50个',
            'type'           => Task::DAILY_TASK,
            'reward'         => [
                'gold'   => 10,
                'ticket' => 0,
            ],
            'max_count'      => 50,
            'review_flow_id' => ReviewFlow::whereName('今日浏览次数')->first()->id,
            'status'         => true,
            'resolve'        => ["visits_type" => "posts", "route" => "学习"],
        ]);
        $task->group = "每日任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '每天胜利5场PK',
            'type'           => Task::DAILY_TASK,
            'reward'         => [
                'gold'   => 30,
                'ticket' => 0,
            ],
            'max_count'      => 50,
            'review_flow_id' => ReviewFlow::whereName('检查今日比赛获胜次数')->first()->id,
            'status'         => true,
        ]);
        $task->group = "每日任务";
        $task->save();

    }

    public function initCustomTasks()
    {
        //自定义任务
        $task = $this->saveTask([
            'name'           => '应用商店好评',
            'details'        => "好评即可获得600智慧奖励！",
            'type'           => Task::CUSTOM_TASK,
            'reward'         => [
                'gold'   => 600,
                'ticket' => 0,
            ],
            'review_flow_id' => null,
            'status'         => true,
            'resolve'        => ["router" => "SubmitTask", "submit_name" => "去评价"],
        ]);
        $task->group = "自定义任务";
        $task->save();

        $task = $this->saveTask([
            'name'           => '试玩同款APP(答妹)',
            'details'        => "书中自有颜如玉，学知识领现金;点击“下载”按钮跳转至应用商店安装APP，并完成注册才能领取奖励。",
            'type'           => Task::CUSTOM_TASK,
            'reward'         => [
                'gold'   => 50,
                'ticket' => 0,
            ],
            'review_flow_id' => null,
            'status'         => true,
            'resolve'        => ["package" => "com.damei", "post_id" => 15053],
        ]);
        $task->group = "自定义任务";
        $task->save();
    }

    public function saveTask(array $data)
    {
        return Task::firstOrCreate($data);
    }
}

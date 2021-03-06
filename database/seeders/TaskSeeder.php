<?php
namespace Database\Seeders;

use Haxibiao\Task\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //需要的旧系统手动truncate
        // DB::table('tasks')->truncate();

        //新人任务
        Task::firstOrCreate([
            'name'           => '完善头像',
            'status'         => Task::ENABLE,
            'details'        => '更改账号头像',
            'type'           => Task::NEW_USER_TASK,
            'reward'         => array("gold" => "10"),
            'resolve'        => array('method' => 'checkUserIsUpdateAvatar', 'router' => 'editInformation'),
            'review_flow_id' => 5,
            'max_count'      => rand(1,10),
        ]);

        Task::firstOrCreate([
            'name'           => '视频发布满15个',
            'status'         => Task::ENABLE,
            'details'        => '去抖音采集15个视频即可领取奖励',
            'type'           => Task::CUSTOM_TASK,
            'reward'         => array("gold" => "10"),
            'resolve'        => array('limit' => '15', 'router' => '', 'method' => 'publicArticleTask'),
            'review_flow_id' => 3,
            'max_count'      => 15,
        ]);

        Task::firstOrCreate([
            'name'           => '绑定手机号',
            'status'         => Task::ENABLE,
            'details'        => '绑定手机号',
            'type'           => Task::NEW_USER_TASK,
            'reward'         => array("gold" => "50"),
            'resolve'        => array('method' => 'checkUserIsUpdatePassAndPhone', 'router' => 'accountBinding'),
            'review_flow_id' => 6,
            'max_count'      => rand(1,10),
        ]);

        Task::firstOrCreate([
            'name'           => '修改性别和生日',
            'status'         => Task::ENABLE,
            'details'        => '修改性别和生日',
            'type'           => Task::NEW_USER_TASK,
            'reward'         => array("gold" => "10"),
            'resolve'        => array('method' => 'checkUserIsUpdateGenderAndBirthday', 'router' => 'editInformation'),
            'review_flow_id' => 7,
            'max_count'      => rand(1,10),
        ]);

        Task::firstOrCreate([
            'name'           => '视频采集悬浮球',
            'details'        => '打开视频采集悬浮球',
            'type'           => Task::CUSTOM_TASK,
            'status'         => Task::DISABLE,
            'resolve'        => array('method' => '', 'router' => 'GoDrinkWater'),
            'review_flow_id' => 0,
            'max_count'      => rand(1,10),
        ]);

        //自定义任务
        Task::firstOrCreate([
            'name'           => '应用商店好评',
            'details'        => '应用商店好评',
            'type'           => Task::CUSTOM_TASK,
            'status'         => Task::DISABLE,
            'resolve'        => array('method' => '', 'router' => 'ToComment'),
            'review_flow_id' => 8,
            'max_count'      => rand(1,10),
        ]);

        Task::firstOrCreate([
            'name'           => '看视频赚钱',
            'details'        => '看视频有机会获得贡献值哦',
            'type'           => Task::DAILY_TASK,
            'status'         => Task::ENABLE,
            'reward'         => array("gold" => "100", "contribute" => "20"),
            'resolve'        => array('method' => '', 'router' => 'MotivationalVideo'),
            'review_flow_id' => 4,
            'max_count'      => 10,
        ]);

        Task::firstOrCreate([
            'name'           => '邀请任务',
            'details'        => '邀请5个新用户，完成注册（需要绑定手机或其他信息）',
            'type'           => Task::CUSTOM_TASK,
            'status'         => Task::ENABLE,
            'reward'         => array("gold" => "600"),
            'review_flow_id' => 11,
            'max_count'      => 5,
        ]);
        Task::firstOrCreate([
            'name'           => '直播任务',
            'details'        => '直播观看人数10+，可领取奖励',
            'type'           => Task::DAILY_TASK,
            'status'         => Task::ENABLE,
            'reward'         => array("gold" => "200"),
            'review_flow_id' => 9,
            'max_count'      => 10,
        ]);
        Task::firstOrCreate([
            'name'           => '作品获赞',
            'details'        => '作品获赞1000+，可领取奖励',
            'type'           => Task::DAILY_TASK,
            'status'         => Task::ENABLE,
            'reward'         => array("gold" => "600"),
            'review_flow_id' => 10,
            'max_count'      => 1000,
        ]);

        Task::firstOrCreate([
            'name'           => 'DrinkWaterAll',
            'details'        => '喝水任务。。。',
            'type'           => Task::DAILY_TASK,
            'status'         => Task::ENABLE,
            'reward'         => array("gold" => "600"),
            'review_flow_id' => 10,
            'max_count'      => 1000,
        ]);
    }
}

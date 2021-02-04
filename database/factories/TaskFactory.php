<?php
namespace Database\Factories;

use Haxibiao\Task\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'    => '应用商店好评 - name',
            'details' => '测试任务详情 - details',
            'max_count' => rand(1,10),
            'type'    => 0, //类型：0:新人任务 1:每日任务 2:成长任务(自定义任务)
            'description' => '测试任务详情 - description',
            'status'  => 1, //状态: 0:删除 1:展示
        ];
    }
}

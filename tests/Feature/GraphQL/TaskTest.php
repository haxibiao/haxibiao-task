<?php

namespace Tests\Feature\GraphQL;

use App\Task;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Haxibiao\Task\Assignment;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TaskTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $likes;
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->task = Task::factory()->create();
    }

    /**
     * 完成任务
     * @group task
     * @group testCompleteTaskMutation
     */
    public function testCompleteTaskMutation()
    {
        $token = $this->user->api_token;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $taskRewardMutation = file_get_contents(__DIR__ . '/Task/CompleteTaskMutation.gql');
        $variables = [
            'id' => $this->task->id,
        ];
        $this->runGuestGQL($taskRewardMutation, $variables, $headers);
    }

    /**
     * 新用户奖励接口
     * @group task
     * @group testNewUserRewordMutation
     */
    public function testNewUserRewordMutation()
    {
        $token = $this->user->api_token;
        $query = file_get_contents(__DIR__ . '/Task/newUserRewordMutation.gql');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
        $variables = [
            'rewardType' => 'VIDEO',
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 领取任务
     * @group task
     * @group testReceiveTaskMutation
     */
    public function testReceiveTaskMutation()
    {
        $token = $this->user->api_token;
        $query = file_get_contents(__DIR__ . '/Task/receiveTaskMutation.gql');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
        $variables = [
            'id' => $this->task->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 提交应用商店好评任务
     * @group task
     * @group testHighPraiseTaskCheckMutation
     */
    // TODO 图片上传存在问题，暂时先注释需要操作图片的
    // public function testHighPraiseTaskCheckMutation()
    // {
    //     $token = $this->user->api_token;
    //     $query = file_get_contents(__DIR__ . '/Task/highPraiseTaskCheckMutation.gql');
    //     $headers = [
    //         'Authorization' => 'Bearer ' . $token,
    //         'Accept' => 'application/json',
    //     ];
    //     $task = Task::whereName('应用商店好评')->first();
    //     //初始化为未提交...
    //     $this->updateTaskStatus($task->id, 0);

    //     //提交好评
    //     $variables = [
    //         'user_id' => $this->user->id,
    //         'account' => '1222222',
    //         'images' => [$this->getBase64ImageString()],
    //         'info' => '测试',
    //     ];

    //     //测试提交是否出错
    //     $this->startGraphQL($query, $variables, $headers);
    // }

    /**
     * 提交应用商店好评任务
     * @group task
     * @group testHighPraiseTaskMutation
     */
    public function testHighPraiseTaskMutation()
    {
        $query = file_get_contents(__DIR__ . '/Task/highPraiseTaskMutation.gql');
        $token = $this->user->api_token;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
        $task = \App\Task::whereName('应用商店好评')->first();
        //初始化为未提交...
        $this->updateTaskStatus($task->id, 0);

        //提交好评
        $variables = [
            'id' => $task->id,
            'content' => '测试好评回复',
        ];

        //测试提交是否出错
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 答复任务
     * @group task
     * @group testReplyTaskMutation
     */
    public function testReplyTaskMutation()
    {
        $token = $this->user->api_token;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        //重置任务为待完成状态
        $this->user->tasks()->detach($this->task->id);
        $this->user->tasks()->attach($this->task->id, ['status' => Task::NEW_USER_TASK]);

        $taskRewardMutation = file_get_contents(__DIR__ . '/Task/ReplyTaskMutation.gql');
        $variables = [
            'id' => $this->task->id,
        ];
        $this->startGraphQL($taskRewardMutation, $variables, $headers);
    }

    /**
     * 任务列表查询
     * @group task
     * @group testTasksQuery
     */
    public function testTasksQuery()
    {

        $token = $this->user->api_token;
        $query = file_get_contents(__DIR__ . '/Task/tasksQuery.gql');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
        //新人任务
        $variables = [
            'type' => 'NEW_USER_TASK',
        ];
        $this->startGraphQL($query, $variables, $headers);

        //每日任务
        $variables = [
            'type' => 'DAILY_TASK',
        ];

        //奖励任务
        $variables = [
            'type' => 'CUSTOM_TASK',
        ];
        $variables = [
            'type' => 'All',
        ];
        $response = $this->startGraphQL($query, $variables, $headers);
        $response->assertJsonFragment(['type' => Task::DAILY_TASK]);
    }

    /**
     * 更新任务状态
     * @group task
     */
    public function updateTaskStatus($task_id, $status)
    {
        $userAssignment = Assignment::where('user_id', $this->user->id)->where('task_id', $task_id)->first();
        if (is_null($userAssignment)) {
            $assignment = new Assignment();
            $assignment->user_id = $this->user->id;
            $assignment->task_id = $task_id;
            $assignment->status = $status;
            $assignment->save();
            return $assignment;
        } else {
            $assignment = Assignment::where('id', $userAssignment->id)->first();
            $assignment->update(['status' => $status]);
        }
    }

    protected function tearDown(): void
    {
        $this->user->forceDelete();
        $this->task->forceDelete();
        parent::tearDown();
    }
}

<?php

namespace Tests\Feature;

use App\Invitation;
use App\User;
use App\UserLive;
use haxibiao\task\Assignment;
use haxibiao\task\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\GraphQL\TestCase;

class TaskTest extends TestCase
{
    use DatabaseTransactions;
    protected $user;

    protected $lives;

    protected $likes;

    protected $invitations;

    protected function setUp(): void
    {
        parent::setUp();

        //最新注册的10个用户里随机一个，方便发现新用户的体验问题...
        // $this->user = User::latest('id')->take(100)->get()->random();

        $this->user  = User::find(2);
        $this->lives = factory(UserLive::class)->create([
            'count_users' => 10,
        ]);

        // $this->likes = factory(Like::class, 1000)->create();

        $this->invitations = factory(Invitation::class, 5)->create(
            ['invited_in' => now()]
        );
    }

    /* --------------------------------------------------------------------- */
    /* ------------------------------- Mutation ----------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * 领取任务
     */
    public function testReceiveTaskMutation()
    {
        $token   = $this->user->api_token;
        $query   = file_get_contents(__DIR__ . '/task/Mutation/receiveTaskMutation.graphql');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $variables = [
            'id' => '1',
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 获取任务奖励
     */
    public function testRewardTaskMutation()
    {
        $token   = $this->user->api_token;
        $query   = file_get_contents(__DIR__ . '/task/Mutation/rewardTaskMutation.graphql');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $variables = [
            'id' => '1',

        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 提交应用商店好评任务
     */
    public function testHighPraiseTaskMutation()
    {

        $token   = $this->user->api_token;
        $query   = file_get_contents(__DIR__ . '/task/Mutation/highPraiseTaskMutation.graphql');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $task = Task::whereName('应用商店好评')->first();
        //初始化为未提交...
        $this->updateTaskStatus($task->id, 0);

        //提交好评
        $variables = [
            'id'      => $task->id,
            'content' => '应用商店好评',
        ];

        //测试提交是否出错
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 直播观看任务领取奖励
     */
    public function testLiveAudienceTaskMutation()
    {
        $token   = $this->user->api_token;
        $query   = file_get_contents(__DIR__ . '/task/Mutation/rewardTaskMutation.graphql');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $task      = Task::whereName('直播任务')->first();
        $variables = [
            'id' => $task->id,
        ];

        //先指派
        $this->updateTaskStatus($task->id, 1);

        //指派中状态的任务，领取奖励.. 应该返回异常
        $this->startGraphQL($query, $variables, $headers);

        //assert json has error
        //assert text has "任务未完成..."
    }

    /* --------------------------------------------------------------------- */
    /* ------------------------------- Query ----------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * 任务列表查询
     */
    public function testTasksQuery()
    {

        $token   = $this->user->api_token;
        $query   = file_get_contents(__DIR__ . '/task/Query/tasksQuery.graphql');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
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
        $response = $this->startGraphQL($query, $variables, $headers);
        //校验存在已指派的看视频赚钱
        $response->assertJsonFragment(['name' => "看视频赚钱"]);

        //校验存在已指派的直播任务
        $tasks = $response->original['data']['tasks'];
        $response->assertJsonFragment(['name' => "直播任务"]);

        //校验存在已指派的邀请任务
        $response->assertJsonFragment(['name' => "邀请任务"]);
        //奖励任务
        $variables = [
            'type' => 'CUSTOM_TASK',
        ];
        $response = $this->startGraphQL($query, $variables, $headers);

        //所有，包含喝水睡觉任务....
        $variables = [
            'type' => 'All',
        ];
        $response = $this->startGraphQL($query, $variables, $headers);
        //检查除了返回新人任务
        //还有邀请（CUSTOM_TASK）
        //$response->assertJsonFragment(['type' => Task::CUSTOM_TASK]); //TODO:目前没有CUSTOM任务 会报错.
        //看视频赚钱(DAILY_TASK)等另外2个类型的任务
        $response->assertJsonFragment(['type' => Task::DAILY_TASK]);
    }

    //更新任务状态
    public function updateTaskStatus($task_id, $status)
    {
        $userAssignment = Assignment::where('user_id', $this->user->id)->where('task_id', $task_id)->first();
        if (is_null($userAssignment)) {
            $assignment          = new Assignment();
            $assignment->user_id = $this->user->id;
            $assignment->task_id = $task_id;
            $assignment->status  = $status;
            $assignment->save();
            return $assignment;
        } else {
            $assignment = Assignment::where('id', $userAssignment->id)->first();
            $assignment->update(['status' => $status]);
        }
    }

    protected function tearDown(): void
    {
        $this->user->lives()->forceDelete();
        $this->user->invitations()->forceDelete();
        parent::tearDown();
    }
}

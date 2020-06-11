<?php

namespace haxibiao\task\Traits;

use App\Exceptions\UserException;
use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use haxibiao\task\Assignment;
use haxibiao\task\Task;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait TaskResolvers
{
    /* --------------------------------------------------------------------- */
    /* ------------------------------- Query ----------------------------- */
    /* --------------------------------------------------------------------- */
    // 获取任务列表
    public static function resolveTasks($root, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo = null)
    {
        $user = getUser();

        //单次查询一个分类的
        $type = $args['type'] ?? 'All';

        if ($type == 'All') {
            if (getAppVersion() < "3.0") {
                $qb = Task::where('type', '<>', Task::CONTRIBUTE_TASK); //贡献任务以前没进入后端任务列表
            } else {
                $qb = Task::all();
            }
        } else {
            $qb = Task::whereType($type);
        }
        $task_ids = $qb->pluck('id');
        //确保指派数据正常
        Assignment::initAssignments($user);

        $assignments = $user->assignments()->with('task')->with('user')
            ->whereIn('task_id', $task_ids)->get();


        //初始化每日任务状态
        Assignment::initDailyTask($assignments);


        $tasks = [];
        foreach ($assignments as $assignment) {

            $task = $assignment->task;
            //过滤掉下架的任务不显示
            if ($task->status == Task::DISABLE) {
                continue;
            }

            //过滤完成后需要不显示的任务 ↓ $record 保存的是 tasks 表主键
            $notShowCompletedIds = Task::whereType(Task::NEW_USER_TASK)->pluck('id')->toArray();
            if ($assignment->status == 3 && in_array($assignment->task_id, $notShowCompletedIds)) {
                continue;
            }

            //指派的 属性alias 过去给gql用
            $task->assignment = $assignment;
            $task->user       = $assignment->user;
            $tasks[]          = $task;
        }
        return $tasks;
    }

    // 喝水打卡任务列表
    public static function resolveDrinkWaterTasks($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user       = getUser();
        $task       = Task::where('name', 'DrinkWaterAll')->first();
        $assignment = $task->getAssignment($user->id);
        if (!$assignment) {
            $assignment = Assignment::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
            ]);
        }

        $resolve = $assignment->resolve;
        return Task::getDrinkWaterSubTasks($resolve);
    }

    // 睡觉打卡玩法获取
    public static function resolveSleepTask($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user      = getUser();
        $sleepTask = Task::where('resolve->task_en', 'Sleep')->first();

        throw_if(is_null($sleepTask), UserException::class, '睡觉任务不存在!');

        $task       = $sleepTask;
        $assignment = Assignment::firstOrCreate([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
        $sleepCompletedAt = $assignment->completed_at;
        $status           = 1;

        if ($sleepCompletedAt >= today()) {

            $minutes       = $sleepTask->resolve['minutes'] ?? 15;
            $diffMinus     = Carbon::parse($sleepCompletedAt)->diffInMinutes();
            $toastDiffTime = Task::toastDiffTime($sleepCompletedAt, $minutes);
            $task->details = empty($toastDiffTime) ? $task->details : $toastDiffTime;

            //没到15分钟,无法打卡,状态为3
            if ($diffMinus < $minutes) {
                $status = 3;
            } else {
                $wakeUpTask          = Task::where('resolve->task_en', 'Wake')->first();
                $currrentIsSleepTask = $sleepTask->status == 1;
                $task                = $currrentIsSleepTask ? $sleepTask : $wakeUpTask;
            }
        }
        $assignment = Assignment::firstOrCreate([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
        $assignment->status = $status;
        $assignment->save();
        return $task;
    }

    /* --------------------------------------------------------------------- */
    /* ------------------------------- Mutation ----------------------------- */
    /* --------------------------------------------------------------------- */

    public static function resolveReceive($root, array $args, $context = null, $info = null)
    {
        $task_id = $args['id'];
        return Task::receiveTask($task_id);
    }

    // 所有喝水完成后的奖励
    public static function resolveDrinkWaterReward($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $userId     = getUserId();
        $task       = Task::where('name', 'DrinkWaterAll')->first();
        $assignment = $task->getAssignment($userId);

        throw_if($assignment->status == Assignment::TASK_DONE, UserException::class, '奖励已经领取');
        throw_if($assignment->status != Assignment::TASK_REACH, UserException::class, '任务还未完成');

        $assignment->status = Assignment::TASK_DONE;
        $assignment->save();

        return $assignment;
    }

    // 睡觉打卡奖励接口（老接口）
    public static function resolveSleepReward($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user       = getUser();
        $task       = Task::find($args['id']);
        $assignment = $task->getAssignment($user->id);
        $isWakeCard = Arr::get($task->resolve, 'task_en') == "Wake";

        $sleepTask       = Task::where('resolve->task_en', 'Sleep')->first();
        $sleepAssignment = $sleepTask->getAssignment($user->id);

        $intervalMinutes = $sleepTask->resolve['minutes'] ?? 15;
        $wakeTask        = Task::where('resolve->task_en', 'Wake')->first();

        $taskOutOfTime = true;
        if ($sleepAssignment->completed_at) {
            $taskOutOfTime = Carbon::parse($sleepAssignment->completed_at)->diffInMinutes() > $intervalMinutes;
        }
        $taskInReview = $assignment->status == Assignment::TASK_REVIEW;

        if ($taskOutOfTime && $taskInReview) {
            $assignment->status       = Assignment::TASK_DONE;
            $assignment->completed_at = now();
            $assignment->content      = $isWakeCard ? $task->getTaskContent() : $task->name . "打卡成功,等待下次" . $wakeTask->name . "时领取奖励";
            $assignment->save();
        }

        //判断此次打卡的是否为起床卡,是起床卡,更改掉睡觉卡的状态为可打卡状态
        if ($isWakeCard) {
            $sleepAssignment->update(['status' => 1]);
            $assignment->increment('current_count');
        }
        return $assignment;
    }

    // 任务中心领取奖励接口
    public static function resolveReward($root, array $args, $context = null, $info = null)
    {
        $task_id = $args['id'];
        $high    = $args['high'];
        return Task::rewardTask($task_id, $high);
    }

    // 喝水任务上报打卡接口 drinkWater,单次喝水成功后调用...
    public static function resolveDrinkWater($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $userId = getUserId();

        // 此处的task_id代表喝的是第几杯水,兼容以前的设计
        $position = $args['id'];

        $task = Task::where('name', 'DrinkWaterAll')->first();

        if (is_null($task)) {
            return new UserException('喝水任务好像不见咯，请刷新后重试');
        }

        $assignment = $task->getAssignment($userId);

        // $resolve存放喝水的信息,如[1,2]代表喝了第一杯和第二杯
        $resolve = $assignment->resolve;

        $subTaskHasDone = $resolve && in_array($position, $resolve);
        throw_if($subTaskHasDone, UserException::class, '已经完成了');

        // 校验第$position杯水是否已经开始
        $hour           = Carbon::now()->hour;
        $taskIsNotStart = ($position + 8 > $hour);
        throw_if($taskIsNotStart, UserException::class, '还未开始');

        if (is_null($resolve)) {
            $resolve = [$position];
        } else {
            $resolve[] = $position;
        }
        $assignment->resolve       = $resolve;
        $assignment->current_count = count($resolve);
        $assignment->save();

        return Task::getDrinkWaterSubTasks($resolve);
    }

    // 应用商店好评任务接口
    public static function resolveHighPariseReply($root, array $args, $context, $info)
    {
        $user = checkUser();
        $task = Task::find($args['id']);
        throw_if(is_null($task), UserException::class, '任务不存在哦~,请稍后再试');
        throw_if(empty(trim($args['content'])), UserException::class, '账号不能为空哦~');

        return Task::highPraise($user, $task, $args['content']);
    }

    //答复任务
    public static function resolveReply($root, array $args, $context = null, $info = null)
    {
        $user    = getUser();
        $task_id = $args['id'];
        $content = $args['content'] ?? '';

        return Task::replyTask($user, $task_id, $content);
    }

    //观看新手教程或采集视频教程任务状态变更
    public static function newUserReword($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        //TODO: 新人教程任务，抖音采集学习任务
        return 1;
    }

    //完成任务
    public static function resolveComplete($root, array $args, $context = null, $info = null)
    {
        $user    = getUser();
        $task_id = $args['id'];

        return Task::completeTask($user, $task_id);
    }
}

<?php

namespace haxibiao\task\Traits;

use App\Action;
use App\Exceptions\GQLException;
use App\Gold;
use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use haxibiao\task\Assignment;
use haxibiao\task\Task;
use haxibiao\task\UserTask;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait TaskResolvers
{
    /* --------------------------------------------------------------------- */
    /* ------------------------------- Query ----------------------------- */
    /* --------------------------------------------------------------------- */
    // 获取任务列表
    public static function resolveTasks($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = getUser();

        //单次查询一个分类的
        $type = $args['type'];

        $task_obj = $type == 'All' ? Task::all() : Task::whereType($type);
        $task_ids = $task_obj->pluck('id');
        //确保指派数据正常
        Assignment::initAssignments($user);

        $assignments = $user->assignments()->with('task')->with('user')
            ->whereIn('task_id', $task_ids)->get();

        if ($type == self::DAILY_TASK) {
            //初始化每日任务状态
            Assignment::initDailyTask($assignments);
        }

        $tasks = [];
        foreach ($assignments as $assignment) {

            $task = $assignment->task;
            //过滤掉下架的任务不显示
            if ($task->status == Task::DISABLE) {
                continue;
            }

            //过滤完成后需要不显示的任务 ↓ $record 保存的是 tasks 表主键
            $notShowCompletedIds = [1, 3, 4, 6];
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

        throw_if(is_null($sleepTask), GQLException::class, '睡觉任务不存在!');

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

    public static function receiveTaskResolver($root, array $args, $context, $info)
    {
        $task = Task::where('id', $args['id'])
            ->first();
        $user       = getUser();
        $assignment = Assignment::firstOrNew([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
        if (!$assignment->id) {
            $assignment->save();
            Action::createAction('tasks', $task->id, $user->id);
        }
        return $task;
    }

    // 所有喝水完成后的奖励
    public static function resolveDrinkWaterReward($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $userId     = getUserId();
        $task       = Task::where('name', 'DrinkWaterAll')->first();
        $assignment = $task->getAssignment($userId);

        throw_if($assignment->status == Assignment::TASK_DONE, GQLException::class, '奖励已经领取');
        throw_if($assignment->status != Assignment::TASK_REACH, GQLException::class, '任务还未完成');

        $assignment->status = Assignment::TASK_DONE;
        $assignment->save();

        return $assignment;
    }

    // 睡觉打卡奖励接口（老接口）
    public static function resolveSleepReward($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user       = getUser();
        $task       = Task::find($args['task_id']);
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
    public static function getReWardResolver($root, array $args, $context, $info)
    {
        $user       = getUser();
        $task       = Task::findOrFail($args['id']);
        $assignment = Assignment::firstOrNew([
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);
        if ($assignment->status == Assignment::TASK_REACH) {
            $assignment->status = Assignment::TASK_DONE;
            $assignment->save();
            $gold   = $task->reward['gold'];
            $remark = sprintf('%s奖励', $task->name);
            Gold::makeIncome($user, $gold, $remark); //发放金币奖励
        }
        return $task;
    }

    // 喝水任务上报打卡接口 drinkWater,单次喝水成功后调用...
    public static function resolveDrinkWater($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $userId = getUserId();

        // 此处的task_id代表喝的是第几杯水,兼容以前的设计
        $position = $args['task_id'];

        $task       = Task::where('name', 'DrinkWaterAll')->first();
        $assignment = $task->getAssignment($userId);

        // $resolve存放喝水的信息,如[1,2]代表喝了第一杯和第二杯
        $resolve = $assignment->resolve;

        $subTaskHasDone = $resolve && in_array($position, $resolve);
        throw_if($subTaskHasDone, GQLException::class, '已经完成了');

        // 校验第$position杯水是否已经开始
        $hour           = Carbon::now()->hour;
        $taskIsNotStart = ($position + 8 > $hour);
        throw_if($taskIsNotStart, GQLException::class, '还未开始');

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
    public static function highPraiseTaskResolver($root, array $args, $context, $info)
    {
        $user = checkUser();

        $task = Task::find($args['id']);
        throw_if(is_null($task), GQLException::class, '任务不存在哦~,请稍后再试');
        throw_if(empty(trim($args['content'])), GQLException::class, '账号不能为空哦~');

        return Task::highPraise($user, $task, $args['content']);
    }

    //观看新手教程或采集视频教程任务状态变更
    public static function newUserReword($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = checkUser();
        $type = $args['type'];
        if ($type === 'newUser') {
            $task             = Task::where("name", "观看新手视频教程")->first();
            $userTask         = UserTask::where("task_id", $task->id)->where("user_id", $user->id)->first();
            $userTask->status = UserTask::TASK_REACH;
            $userTask->save();
            return 1;
        } else if ($type === 'douyin') {
            $task             = Task::where("name", "观看采集视频教程")->first();
            $userTask         = UserTask::where("task_id", $task->id)->where("user_id", $user->id)->first();
            $userTask->status = UserTask::TASK_REACH;
            $userTask->save();
            return 1;
        }
        return -1;
    }
}

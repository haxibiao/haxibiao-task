<?php

namespace Haxibiao\Task\Traits;

use App\Assignment;
use App\Task;
use Haxibiao\Task\ReviewFlow;

trait PlayWithTasks
{

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'assignments')
            ->withPivot(['status', 'current_count', 'id'])
            ->withTimestamps();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class)->latest('id');
    }

    public function getAssignment($task_id)
    {
        return $this->assignments()->where('task_id', $task_id)->first();
    }

    /**
     * 检查并更新任务状态
     */
    public function reviewTasksByClass($class)
    {
        $class = last(explode("\\", $class));
        $user  = $this;
        //判断新手任务是否完成 //FIXME: 注意性能问题，注意单词mutation触发过多sql 查询和update
        $tasks = $user->getTasksByReviewClass($class);
        //在这里就批量插入完成,会降低脏读几率,导致插入unique报错
        $insertData = [];
        foreach ($tasks as $task) {
            $insertData[] = ['task_id' => $task->id, 'user_id' => $user->id];
        }
        Assignment::insertOrIgnore($insertData);
        $assignments = $user->assignments()->whereIn('task_id', $tasks->pluck('id'))->get();
        foreach ($tasks as $task) {
            $assignment = $assignments->firstWhere('task_id', $task->id);
            //检查更新任务指派状态
            $task->checkTaskStatus($user, $assignment);
        }

        // //提现任务
        // if ($withdraw->status == Withdraw::SUCCESS_WITHDRAW) {
        //     //获取任务
        //     $tasks = $user->new_user_first_withdraw;
        //     if (!empty($tasks)) {
        //         //更新指派任务状态
        //         foreach ($tasks as $task) {
        //             $task->checkTaskStatus($user);
        //         }
        //     }
        // }

        // //刷...
        // $tasks = $user->visit_tasks;
        // foreach ($tasks as $task) {
        //     $task->checkTaskStatus($user);
        // }

    }

    public function getTasksByReviewClass($class)
    {
        $tasks = Task::with(['review_flow'])->whereIn('review_flow_id', function ($query) use ($class) {
            $query->select('id')
                ->from((new ReviewFlow)->getTable())
                ->where('review_class', $class);
        })->get();

        return $tasks;
    }

    //新手任务
    public function getNewUserTasks()
    {
        return Task::whereType(0)->get();
    }

    /**
     * 获取指定任务 通用方法（每日任务）
     **/
    public function getCommonTasks(String $name)
    {
        return Task::whereName($name)->get();
    }

    //直播任务
    public function getLiveTasks()
    {
        return Task::whereName('直播任务')->get();
    }

    public function getLikeTasksAttribute()
    {
        //邀请类的任务
        return Task::whereName('作品获赞')->get();
    }

    public function getInvitationTasksAttribute()
    {
        //邀请类的任务
        return Task::whereName('邀请任务')->get();
    }

    public function getArticleTasksAttribute()
    {
        //目前只有一个发布类的任务
        return Task::whereName('视频发布满15个')->get();
    }

    public function getAnswerTasksAttribute()
    {
        $tasks = [];
        //答题类任务 //FIXME: 还有新冠答题...
        $flow = ReviewFlow::whereName('答题总数')->first();
        if ($flow) {
            $tasks = Task::where('review_flow_id', $flow->id)->get();
        }
        return $tasks;
    }

    public function getCategoryAnswerQuestionTasksAttribute()
    {
        //分类答题任务
        return Task::whereName('新型肺炎防治答10题')->get();
    }
}

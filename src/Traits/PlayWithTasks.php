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
        foreach ($tasks as $task) {
            //检查更新任务指派状态
            $task->checkTaskStatus($user);
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
        $flow_ids = ReviewFlow::where('review_class', $class)->pluck('id');
        $tasks    = Task::whereIn('review_flow_id', $flow_ids)->get();
        return $tasks;
    }

    //新手任务
    public function getNewUserTasks()
    {
        return Task::whereType(0)->get();
    }

    //直播任务
    public function getUserLiveTasks()
    {
        return Task::whereName('直播任务')->get();
    }

    //评论任务
    public function getCommentTasks()
    {
        return Task::whereName('评论高手')->get();
    }

    //点赞他人任务
    public function getLikeActionTasks()
    {
        return Task::whereName('点赞超人')->get();
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

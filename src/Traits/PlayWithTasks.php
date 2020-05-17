<?php

namespace haxibiao\task\Traits;

use haxibiao\task\Assignment;
use haxibiao\task\Task;

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
        return $this->hasMany(Assignment::class);

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
}

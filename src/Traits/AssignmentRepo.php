<?php

namespace Haxibiao\Task\Traits;

use App\Jobs\AuditTaskCheck;
use App\Task;

trait AssignmentRepo
{

    public static function initContributeTask($assignments)
    {
        foreach ($assignments as $assignment) {

            $task = $assignment->task;
            //每日任务: 重置刷新状态和进度
            if (!is_null($task) && $task->isContributeTask()) {
                //新的一天开始
                if ($assignment->updated_at < today()) {
                    $assignment->progress      = 0;
                    $assignment->completed_at  = null;
                    $assignment->resolve       = null;
                    $assignment->current_count = 0;
                    $assignment->status        = 0;
                    $assignment->save();
                }
            }
        }
    }

    //初始化用户的任务指派
    public static function initAssignments($user)
    {
        $task_ids          = Task::enabled()->pluck('id')->toArray();
        $assigned_task_ids = $user->assignments()->pluck('task_id')->toArray();

        //$assigned_task_ids = array_unique($assigned_task_ids);
        $needSyncTasks = count(array_diff($task_ids, $assigned_task_ids)) ||
        count(array_diff($assigned_task_ids, $task_ids));

        if ($needSyncTasks) {
            //指派所有可指派的任务,更新任务列表，符合当前任务系统版本要求
            //$task_ids = array_unique($task_ids);
            $user->tasks()->sync($task_ids);
        }
    }

    public static function initDailyTask($assignments)
    {
        foreach ($assignments as $assignment) {

            $task = $assignment->task;
            //每日任务: 重置刷新状态和进度
            if (!is_null($task) && $task->isDailyTask()) {
                //新的一天开始
                if ($assignment->updated_at < today()) {
                    $assignment->progress      = 0;
                    $assignment->completed_at  = null;
                    $assignment->resolve       = null;
                    $assignment->current_count = 0;
                    $assignment->status        = 1;
                    $assignment->save();
                }
            }
        }
    }

    public static function initWeekTask($assignments)
    {
        foreach ($assignments as $assignment) {

            $task = $assignment->task;
            //每周任务: 重置刷新状态和进度
            if (!is_null($task)) {
                $startOfWeek = null;
                //新的一周开始了
                if ($task->isWeekTask()) {
                    $startOfWeek = now()->startOfWeek();
                } else if ($task->isLoopWeekTask()) {
                    $startOfWeek = now()->subWeek();
                    if (str_contains($task->name, '审题任务')) {
                        //七天后没有完成降级
                        dispatch(new AuditTaskCheck($assignment))->delay(now()->addWeek());
                    }
                }

                if ($startOfWeek && $assignment->updated_at < $startOfWeek) {
                    $assignment->progress      = 0;
                    $assignment->completed_at  = null;
                    $assignment->resolve       = null;
                    $assignment->current_count = 0;
                    $assignment->status        = 1;
                    $assignment->save();
                }
            }
        }
    }

    /**
     * 获取任务流的执行结果
     *
     * @return bool
     */
    public function getTaskFlowsResult()
    {

        $flows = $this->review_info['flows'];
        foreach ($flows as $flow) {

            $result = $this->$flow();
            // 排队执行flows。单个flow执行失败整个flows失败。
            if (!$result) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $assignments
     * 保证任务系统上线前已完成个人信息设置和手机号绑定的用户也可以领取奖励
     */
    public static function initNewUserTask($assignments)
    {
        foreach ($assignments as $assignment) {
            $user             = $assignment->user;
            $profile          = $user->profile;
            $user->updated_at = now();
            $user->save();
            $profile->updated_at = now();
            $profile->save();

        }
    }
}

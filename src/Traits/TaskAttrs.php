<?php

namespace haxibiao\task\Traits;

use haxibiao\task\Task;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

trait TaskAttrs
{

    /**
     * 获取任务配置信息
     * @return mixed
     */
    public function getTaskInfoAttribute()
    {
        return json_encode($this->resolve);
    }

    /**
     * 获取任务奖励信息
     * @return array
     */
    public function getRewardInfoAttribute()
    {
        $json = $this->reward;
        return [
            'gold'       => array_get($json, "gold"),
            'contribute' => array_get($json, "contribute"),
            'ticket'     => array_get($json, "ticket"),
        ];
    }

    /**
     * 获取任务的类型（中文）
     * @return mixed|string
     */
    public function getTaskClassAttribute()
    {
        return Task::getTypes()[$this->type];
    }

    /**
     * 获取睡眠状态
     * @return bool
     */
    public function getSleepStatusAttribute()
    {
        if (Arr::get($this->resolve, 'task_en') == 'Sleep') {
            return false;
        }
        return true;
    }

    /**
     * 获取任务开始时间
     * @return false|string
     */
    public function getStartTimeAttribute()
    {
        return date("H:i", strtotime($this->start_at));
    }

    /**
     * 获取任务结束时间
     * @return false|string
     */
    public function getEndTimeAttribute()
    {
        return date("H:i", strtotime($this->end_at));
    }

    /**
     * 获取任务背景图
     * @return false|string
     */
    public function getBackgroundImgAttribute()
    {
        if (!$this && $this->background_img) {
            return Storage::cloud()->url($this->background_img);
        }
    }

    /**
     * 获取任务Icon
     * @return false|string
     */
    public function getIconUrlAttribute()
    {
        if ($this && $this->icon) {
            return Storage::cloud()->url($this->icon);
        }
    }

    /* --------------------------------------------------------------------- */
    /* ------------------------- 答赚App独特的Attrs ------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * @deprecated 3.x 后前端自己改json字段resolve
     */
    public function getRouterAttribute()
    {
        return Arr::get($this->resolve, 'router', '');
    }

    /**
     * @deprecated 3.x 后前端自己改json字段resolve
     */

    public function getRouteAttribute()
    {
        $route = Arr::get($this->resolve, 'route');
        return !empty($route) ? $route : $this->router;
    }

    /**
     * @deprecated 3.x 后前端自己改json字段resolve
     */

    public function getPackageAttribute()
    {
        return Arr::get($this->resolve, 'package');
    }

    /**
     * @deprecated 3.x 后前端自己改json字段resolve
     */

    public function getPostIdAttribute()
    {
        return Arr::get($this->resolve, 'post_id');
    }

    /* --------------------------------------------------------------------- */
    /* ----------- 下列函数可以随着GQL中TaskType的丢弃而移除 ------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * 指派的进度说明
     * @return string|null
     */
    public function getProgressDetailsAttribute()
    {
        //resolvers里会关联这个属性回来
        if ($this->max_count != 0 && isset($this->assignment)) {
            $count = $this->assignment->current_count;
            return $count . " / " . $this->max_count;
        }

        return null;
    }

    //指派的完成领取状态
    public function getTaskStatusAttribute()
    {
        if (isset($this->assignment)) {
            return $this->assignment->status;
        }
        // 没有指派任务，用户应该可以自己领取
        return 0;
        // return 1; //已指派
    }

    //判断任务的进度条(喝水杯子UI)
    public function getTaskProgressAttribute()
    {
        if (isset($this->assignment)) {
            return $this->assignment->progress;
        }
        return 0;
    }

    //这个名称交给前端兄弟去决定
    public function getSubmitNameAttribute()
    {
        $submit     = $this->task_status;
        $submitName = "";
        switch ($submit) {
            case 1:
                $submitName = "进行中";
                break;
            case 2:
                $submitName = "领取";
                break;
            case 3:
                $submitName = "已完成";
                break;
            default:
                $submitName = "";
        }
        return $submitName;
    }
}

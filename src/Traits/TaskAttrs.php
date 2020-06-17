<?php

namespace haxibiao\task\Traits;

use App\Assignment;
use haxibiao\task\Task;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

trait TaskAttrs
{
    public function getNextRewardVideoTimeAttribute()
    {
        if ($this->name == '有趣小视频') {
            if ($user = checkUser()) {
                $created_at = $user->getLatestWatchRewardVideoTime();
                if (empty($created_at)) {
                    return 0;
                }
                $leftTime = now()->diffInSeconds($created_at);

                return $leftTime >= 30 ? 0 : 30 - $leftTime;
            }
            return 0;
        }
    }

    // public function getGroupAttribute()
    // {
    //     switch ($this->type) {
    //         case 0:
    //             return "新人任务";
    //             break;
    //         case 1:
    //             return "每日任务";
    //             break;
    //         case 3:
    //             return "实时任务"; //工厂的喝水睡觉
    //             break;
    //         case 4:
    //             return "贡献任务";
    //             break;
    //         default:
    //             return "自定义任务"; //type=2
    //             break;
    //     }
    // }

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
        //这里可以控制奖励信息的特殊显示，比如激励视频的...
        return $this->reward;
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
    /* ----------- 下列函数可以随着GQL中答赚TaskType的丢弃而移除 ------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * 指派的进度说明 (3/20) 这样
     * @return string|null
     */
    public function getProgressDetailsAttribute()
    {
        //resolvers里会关联这个属性回来
        if ($this->max_count != 0 && isset($this->assignment)) {
            $count = $this->assignment->current_count;
            //如果当前完成进度等于最大完成次数 不超过计数  保证不出现 5/1 这种出现
            $count ==  $this->max_count ? $this->max_count : $count;
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
        return Assignment::TASK_UNDONE;
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
}

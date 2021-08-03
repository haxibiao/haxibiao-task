<?php

namespace Haxibiao\Task\Traits;

use App\Assignment;
use App\Collection;
use Haxibiao\Task\Task;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait TaskAttrs
{
    public function getNextRewardVideoTimeAttribute()
    {
        if ($this->name == '有趣小视频' || $this->name == '看视频赚钱') {
            if ($user = currentUser()) {
                $last_reward_video_time = $user->profile->last_reward_video_time;
                if ($last_reward_video_time) {
                    $created_at = $last_reward_video_time ?? now();
                    if (empty($created_at)) {
                        return 0;
                    }
                    $leftTime = now()->diffInSeconds($created_at);
                    return $leftTime >= 30 ? 0 : 30 - $leftTime;
                }
                //没有上次观看记录的，下次观看间距时间为0
                else {
                    return 0;
                }
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
        //这里可以控制奖励信息的特殊显示，比如激励视频的...
        $reward    = $this->reward;
        $isDazhuan = config('app.name') == 'datizhuanqian';
        if ($this->name == '有趣小视频' && $isDazhuan) {
            $user = currentUser();
            if (!is_null($user) && array_key_exists('contribute_high', $reward)) {
                $reward['contribute_high'] = $user->withdrawCount >= 7 ? 4 : $reward['contribute_high'];
            }
        }

        return $reward;
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
            return cdnurl($this->background_img);
        }
    }

    /**
     * 获取任务Icon
     * @return false|string
     */
    public function getIconUrlAttribute()
    {
        if (Str::contains($this->icon, "http")) {
            return $this->icon;
        }
        if ($this && $this->icon) {
            return cdnurl($this->icon);
        }
    }

    /**
     * 获取任务Icon
     * @return false|string
     */
    public function getIconAttribute($value)
    {
        if (Str::contains($value, "http")) {
            return $value;
        }
        if ($this && $value) {
            return cdnurl($value);
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
            $count = $count >= $this->max_count ? $this->max_count : $count;
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

    public function getCollectionAttribute()
    {

        if ($this->relation_class == self::COLLECTION && isset($this->task_object)) {
            return Collection::whereIn('id', $this->task_object)->first();
        }
        return null;
    }
}

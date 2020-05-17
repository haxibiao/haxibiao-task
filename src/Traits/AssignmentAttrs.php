<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/4/26
 * @Version
 */

namespace haxibiao\task\Traits;

use haxibiao\task\Assignment;

trait AssignmentAttrs
{
    /**
     * 获取当前任务的详细进度
     *
     * @return mixed
     */
    public function getProgressAttribute()
    {
        return $this->getAttributes()['progress'];
    }

    /**
     * 获取任务的状态（中文）
     * @return mixed|string
     */
    public function getSubmitNameAttribute()
    {
        return Assignment::getTypes()[$this->status];
    }
}

<?php

namespace Haxibiao\Task\Traits;

use Illuminate\Support\Arr;

/**
 * @deprecated 兼容答赚以前任务系统里的属性
 */
trait TaskOldAttrs
{

    public function getGoldAttribute()
    {
        return Arr::get($this->reward, 'gold', 0);
    }

    public function getTicketAttribute()
    {
        return Arr::get($this->reward, 'ticket', 0);
    }

    public function getRouterAttribute()
    {
        return Arr::get($this->resolve, 'router', '');
    }

    public function getRouteAttribute()
    {
        $route = Arr::get($this->resolve, 'route');
        return !empty($route) ? $route : $this->router;
    }

    public function getSubmitNameAttribute()
    {
        return Arr::get($this->resolve, 'submit_name', '做任务');
    }

    public function getPackageAttribute()
    {
        return Arr::get($this->resolve, 'package');
    }

    public function getPostIdAttribute()
    {
        return Arr::get($this->resolve, 'post_id');
    }

    public function getUserTaskStatusAttribute()
    {
        return $this->assignment ? $this->assignment->status : 0;
    }
}

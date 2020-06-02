<?php

namespace haxibiao\task;

use App\User;
use haxibiao\task\Assignment;
use haxibiao\task\ReviewFlow;
use haxibiao\task\Traits\TaskAttrs;
use haxibiao\task\Traits\TaskMethod;
use haxibiao\task\Traits\TaskOldAttrs;
use haxibiao\task\Traits\TaskRepo;
use haxibiao\task\Traits\TaskResolvers;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use TaskRepo;
    use TaskAttrs;
    use TaskOldAttrs;
    use TaskResolvers;
    use TaskMethod;

    protected $fillable = [
        'id',
        'name',
        'details',
        'logo',
        'type',
        'status',
        'icon',
        'reward',
        'resolve',
        'review_flow_id',
        'max_count',
    ];

    protected $casts = [
        'start_at'    => 'datetime',
        'end_at'      => 'datetime',
        'reward'      => 'array',
        'resolve'     => 'array',
        'review_info' => 'array',
    ];

    //任务类型
    const NEW_USER_TASK   = 0;
    const DAILY_TASK      = 1;
    const CUSTOM_TASK     = 2;
    const TIME_TASK       = 3;
    const CONTRIBUTE_TASK = 4;

    //任务状态
    const ENABLE  = 1;
    const DISABLE = 0;

    public function scopeEnabled($query)
    {
        return $query->whereStatus(self::ENABLE);
    }

    public function reviewFlow()
    {
        return $this->belongsTo(ReviewFlow::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->using(Assignment::class)
            ->withPivot(['id', 'content', 'current_count'])
            ->withTimestamps();
    }
    public static function getTypes()
    {
        return [
            self::NEW_USER_TASK => '新人任务',
            self::DAILY_TASK    => '日常任务',
            self::CUSTOM_TASK   => '自定义任务',
            self::TIME_TASK     => '实时任务',
        ];
    }

    public static function getStatuses()
    {
        return [
            self::ENABLE  => '已展示',
            self::DISABLE => '未展示',
        ];
    }
}

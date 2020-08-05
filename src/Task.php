<?php

namespace Haxibiao\Task;

use App\User;
use Haxibiao\Task\Assignment;
use Haxibiao\Task\ReviewFlow;
use Haxibiao\Task\Traits\TaskAttrs;
use Haxibiao\Task\Traits\TaskMethod;
use Haxibiao\Task\Traits\TaskOldAttrs;
use Haxibiao\Task\Traits\TaskRepo;
use Haxibiao\Task\Traits\TaskResolvers;
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
        'group',
        'desciption',
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
    const TIME_TASK       = 3; //喝水8次（限制频率），睡觉无限次（限制频率）
    const CONTRIBUTE_TASK = 4; //看激励视频，出题等有贡献获取的任务
    const WEEK_TASK = 5; //新增周任务
    const GROUP_TASK = 6; //复合任务

    //任务状态
    const ENABLE  = 1;
    const DISABLE = 0;

    public function scopeEnabled($query)
    {
        return $query->whereStatus(self::ENABLE);
    }

    public function review_flow()
    {
        return $this->belongsTo(ReviewFlow::class, 'review_flow_id');
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
            self::WEEK_TASK     => '每周任务',
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

<?php

namespace Haxibiao\Task;

use App\User;
use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Content\Collection;
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
    use HasFactory;

    public $guarded = [];

    //任务操作行为
    const VISIT_ACTION     = 'visited';
    const LIKE_ACTION      = 'liked';
    const COMMENT_ACTION   = 'commented';
    const FAVORABLE_ACTION = 'favorable';

    //任务操作类
    const POST       = 'posts';
    const COLLECTION = 'collections';
    const USER       = 'users';
    const MOVIE      = 'movies';
    const ARTICLE    = 'articles';

    protected $casts = [
        'start_at'    => 'datetime',
        'end_at'      => 'datetime',
        'reward'      => 'array',
        'resolve'     => 'array',
        'review_info' => 'array',
        'task_object' => 'array',
    ];

    //任务类型
    const NEW_USER_TASK   = 0;
    const DAILY_TASK      = 1;
    const CUSTOM_TASK     = 2;
    const TIME_TASK       = 3; //喝水8次（限制频率），睡觉无限次（限制频率）
    const CONTRIBUTE_TASK = 4; //看激励视频，出题等有贡献获取的任务
    const WEEK_TASK       = 5; //周任务（now startOfWeek）
    const LOOP_WEEK_TASK  = 7; //周任务(now subWeek)
    const GROUP_TASK      = 6; //复合任务

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

    public static function getActions()
    {
        return [
            self::LIKE_ACTION      => '点赞',
            self::COMMENT_ACTION   => '评论',
            self::VISIT_ACTION     => '浏览',
            self::FAVORABLE_ACTION => '收藏',
        ];
    }
    public static function getActionClasses()
    {
        return [
            self::POST       => '动态',
            self::USER       => '用户',
            self::COLLECTION => '集合',
            self::MOVIE      => '电影',
        ];
    }

    public function getCollectionAttribute()
    {

        if ($this->relation_class == self::COLLECTION && isset($this->task_object)) {
            return Collection::whereIn('id', $this->task_object)->first();
        }
        return null;
    }
}

<?php

namespace Haxibiao\Task\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Haxibiao\Breeze\Helpers\Redis\RedisHelper;

class ReviewTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;
    private $class;
    private $server;

    const Queue = 'tasks';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $class)
    {
        $this->onQueue(self::Queue);
        $this->userId = $userId;
        $this->class  = $class;
        $this->server = gethostname();

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //记录一下 每日触发次数
        $redis = RedisHelper::redis();
        if ($redis) {
            $now  = microtime(true);
            $prec = 86400;
            $pnow = intval($now / $prec) * $prec;
            $redis->hincrby('tasks:jobs:counter', $pnow, 1);
        }

        $user = User::find($this->userId);
        if (!is_null($user)) {
            //触发任务检测
            $user->reviewTasksByClass($this->class);
        }
    }

}

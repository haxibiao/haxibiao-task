<?php

namespace haxibiao\task\Jobs;

use haxibiao\task\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class DelayRewaredTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $assignment_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($assignment_id)
    {
        $this->assignment_id = $assignment_id;
        $this->onQueue('reward');
        $this->delay(30); //延迟自动审核
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $assignment = Assignment::find($this->assignment_id);
        if ($assignment) {
            $assignment->status = Assignment::TASK_REACH; //自动达标，需要用户自动领取奖励，
            $assignment->save();
        }
    }
}

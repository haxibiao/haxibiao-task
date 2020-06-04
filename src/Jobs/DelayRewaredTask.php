<?php

namespace haxibiao\task\Jobs;

use haxibiao\task\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DelayRewaredTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $assignment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($assignment_id)
    {
        $this->assignment = Assignment::find($assignment_id);
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
        $assignment = $this->assignment;
        if ($assignment) {
            $assignment->status = Assignment::TASK_REACH; //自动达标，需要用户自动领取奖励，
            $assignment->save();
        }
    }
}

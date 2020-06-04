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
            $assignment->status = Assignment::TASK_DONE;
            $assignment->save();
        }
    }
}

<?php

namespace Haxibiao\Task;

use Illuminate\Database\Eloquent\Model;

class StageInvitation extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'reward_rate',
    ];
}

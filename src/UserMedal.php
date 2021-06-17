<?php

namespace Haxibiao\Task;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserMedal extends Pivot
{
    protected $fillable = [
        'user_id',
        'medal_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medal()
    {
        return $this->belongsTo(Medal::class);
    }
}

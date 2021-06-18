<?php

namespace Haxibiao\Task;

use Haxibiao\Task\Traits\MedalAttrs;
use Haxibiao\Task\Traits\MedalRepo;
use Haxibiao\Task\Traits\MedalResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Medal extends Model
{
    use MedalAttrs;
    use MedalRepo;
    use MedalResolvers;

    protected $fillable = [
        'name',
        'score',
        'status',
        'count',
        'data',
    ];

    protected $appends = ['name_cn'];

    protected $casts = [
        'data' => 'array',
    ];

    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable', 'imageable')->withPivot('created_at');
    }
}

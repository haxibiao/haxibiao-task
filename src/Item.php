<?php

namespace Haxibiao\Task;

use Haxibiao\Breeze\Traits\ModelHelpers;
use Haxibiao\Task\Traits\ItemRepo;
use Haxibiao\Task\Traits\ItemResolvers;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use ModelHelpers;
    use ItemResolvers;
    use ItemRepo;

    protected $fillable = [
        'name',
        'description',
        'gold',
        'resolve_functions',
        'count',
        'alias',
    ];

    public function scopeFree($query)
    {
        return $query->where('gold', '<', 1);
    }

    public function scopeAlias($query, $value)
    {
        return is_array($value) ? $query->whereIn('alias', $value) : $query->where('alias', $value);
    }
}

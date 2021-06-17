<?php

namespace Haxibiao\Task\Traits;

use Haxibiao\Task\Medal;

trait MedalResolvers
{
    public function resolveMedals($root, $args, $context, $info)
    {
        return Medal::getMedals($args['user_id']);
    }
}

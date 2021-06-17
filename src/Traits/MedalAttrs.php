<?php
namespace Haxibiao\Task\Traits;

use Illuminate\Support\Facades\Storage;

trait MedalAttrs
{
    public function getDoneIconUrlAttribute()
    {
        return Storage::cloud()->url(data_get($this->data, 'done_icon', null));
    }

    public function getUnDoneIconUrlAttribute()
    {
        return Storage::cloud()->url(data_get($this->data, 'un_done_icon', null));
    }

    public function getNameCnAttribute()
    {
        $names = [
            'datiwangzhe'    => '答题王者',
            'caigaobadou'    => '才高八斗',
            'wenrenmoke'     => '文人墨客',
            'yaochanwanguan' => '腰缠万贯',
            'mingyangsihai'  => '名扬四海',
            'duguqiubai'     => '独孤求败',
        ];

        return data_get($names, $this->name, '未知勋章');
    }
}

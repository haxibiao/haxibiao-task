<?php

namespace Haxibiao\Task\Traits;

trait ContributeAttrs
{
    //Attributes

    public function getReasonAttribute()
    {
        $reason = '';
        $type   = $this->contributed_type;

        switch ($type) {
            case 'questions':
                $reason = '出题成功奖励';
                break;
            case 'comments':
                $reason = '有价值的评论奖励';
                break;
            case 'reports':
                $reason = '举报成功奖励';
                break;
            case 'audits':
                $reason = '审题正确奖励';
                break;
            case 'users':
                $reason = '用户行为贡献';
                break;
            case 'system':
                $reason = '系统奖励';
                break;
            case 'sign_ins':
                $reason = '签到奖励';
                break;
            case 'sign_ins_additional':
                $reason = '签到额外奖励';
                break;
            case 'sign_ins_double_reward':
                $reason = '签到双倍奖励';
                break;
            case 'likes':
                $reason = '点赞奖励';
                break;
            case 'assignments':
                $reason = '任务达成奖励';
                break;
            case 'reward_videos':
                $reason = '激励视频奖励';
                break;
            case 'click_draw_feed':
                $reason = '学习视频奖励';
                break;
            case 'click_feed':
                $reason = '信息流奖励';
                break;
        }
        if ($this->amount <= 0) {
            $reason = ($type == "questions") ? "题目移除" : "评论移除";
        }

        return $reason;
    }

    public function getTimeAttribute()
    {
        return time_ago($this->created_at);
    }
}

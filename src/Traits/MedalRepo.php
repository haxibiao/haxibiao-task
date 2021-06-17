<?php
namespace Haxibiao\Task\Traits;

use App\User;
use Haxibiao\Breeze\Events\NewMedal;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Breeze\Notifications\NewMedalsNotification;
use Haxibiao\Breeze\UserProfile;
use Haxibiao\Sns\Comment;
use Haxibiao\Task\Medal;
use Illuminate\Support\Facades\Log;

trait MedalRepo
{

    public static function getMedal($id)
    {
        return Medal::find($id);
    }

    /**
     * 用户的全部勋章信息
     */
    public static function getMedals($userId)
    {
        $isMe      = false;
        $loginUser = checkUser();
        if (!is_null($loginUser)) {
            $isMe = $loginUser->user_id == $userId;
        }
        $user = $isMe ? $loginUser : User::find($userId);
        throw_if(is_null($user), UserException::class, '获取失败,用户不存在!');

        $userMedals = $user->medals()->get();
        $medals     = Medal::all();
        foreach ($medals as $medal) {
            if (!$isMe && $userMedals->firstWhere('id', $medal->id)) {
                $medal->owned = true;
                continue;
            }

            $progress        = self::medalCheck($user, $medal);
            $medal->progress = $progress;
            //勋章检测
            if ($progress >= 1) {
                try {
                    $user->medals()->attach($medal);
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }
                $user->notify(new NewMedalsNotification($medal));
                //echo广播
                event(new NewMedal($medal, $user->id));
                $medal->increment('count');
                $medal->owned = true;
            }
        }

        return $medals;
    }

    public static function medalCheck($user, $medal)
    {
        //XXX:可以优化成异步
        $profile = UserProfile::firstOrCreate(['user_id' => $user->id]);
        $rules   = data_get($medal->data, 'rules', []);

        //FIXME:多个rules存在只取完成进度多的
        $progress = 0;
        //XXX:优化这堆意大利苗条
        foreach ($rules as $rule => $value) {
            //检查回答总数
            if ($rule == 'correct_answers_count') {
                if ($progress < $profile->correct_count / $value) {
                    $progress = $profile->correct_count / $value;
                }
                continue;
            }

            //出题数
            if ($rule == 'questions_count') {
                if ($progress < $profile->questions_count / $value) {
                    $progress = $profile->questions_count / $value;
                }
                continue;
            }

            //智慧点
            if ($rule == 'gold') {
                if ($progress < $user->gold / $value) {
                    $progress = $user->gold / $value;
                }
                continue;
            }

            //题目获赞数
            if ($rule == 'question_likes_count') {
                $question_likes_count = $user->questions()->sum('count_likes');
                if ($progress < $question_likes_count / $value) {
                    $progress = $question_likes_count / $value;
                }
                continue;
            }

            //评论数
            if ($rule == 'comments_count') {
                $comments_count = $user->comments()->count();
                if ($progress < $comments_count / $value) {
                    $progress = $comments_count / $value;
                }
                continue;
            }

            //获回复数
            if ($rule == 'replys_count') {
                $commentIds  = $user->comments()->pluck('id');
                $replysCount = Comment::whereIn('reply_id', $commentIds)->count();
                if ($progress < $replysCount / $value) {
                    $progress = $replysCount / $value;
                }
                continue;
            }

            //粉丝数
            if ($rule == 'followers_count') {
                if ($progress < $user->followers_count / $value) {
                    $progress = $user->followers_count / $value;
                }
                continue;
            }

            //Pk数
            if ($rule == 'games_count') {
                $games_count = $user->games()->count();
                if ($progress < $games_count / $value) {
                    $progress = $games_count / $value;
                }
                continue;
            }

            //PK胜利数
            if ($rule == 'game_wins_count') {
                $game_wins_count = $user->gameWinners()->count();
                if ($progress < $game_wins_count / $value) {
                    $progress = $game_wins_count / $value;
                }
                continue;
            }
        }

        return $progress;
    }
}

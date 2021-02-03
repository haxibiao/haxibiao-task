<?php

namespace Haxibiao\Task\Traits;

use App\Contribute;
use App\Feedback;
use App\Gold;
use App\Image;
use App\User;
use Carbon\Carbon;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Task\Assignment;
use Haxibiao\Task\Jobs\DelayRewaredTask;
use Haxibiao\Task\Task;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait TaskRepo
{

    public static function getAssignments($user, $type)
    {
        //类型
        if ($type == 'All') {
            if (getAppVersion() < "3.0") {
                $qb = Task::where('type', '<>', Task::CONTRIBUTE_TASK); //贡献任务以前没进入后端任务列表
                //3.0之前的任务不显示新手答题和首次提现任务
                $qb->where('name', '<>', '新手答题')->where('name', '<>', '首次提现奖励');
            } else {
                $qb = Task::all();
            }
        } else {
            $qb = Task::whereType($type);
        }
        $task_ids = $qb->pluck('id');

        //确保指派数据正常
        Assignment::initAssignments($user);
        $assignments = $user->assignments()->with('task.review_flow')->with('user')
            ->whereIn('task_id', $task_ids)->get();

        //初始化每日任务状态
        Assignment::initDailyTask($assignments);

        //初始化每周任务状态
        Assignment::initWeekTask($assignments);

        //初始化有趣小视频任务状态(每日刷新)以后也是贡献任务
        Assignment::initContributeTask($assignments);

        //更新新人任务状态
        Assignment::initNewUserTask($assignments);

        //过滤
        $assignments = $assignments->filter(function ($assignment, $key) {
            $task = $assignment->task;
            $take = !is_null($task);
            if ($take) {
                $user = $assignment->user;
                //已领取的,新人和自定义任务不再返回前端显示
                if (in_array($task->type, [Task::NEW_USER_TASK, Task::CUSTOM_TASK])) {
                    $take = $assignment->status != Assignment::TASK_DONE;
                }

                //已下架的,过滤掉不显示
                if ($task->status == Task::DISABLE) {
                    $take = false;
                }

                //过滤两个新人任务 老用户不让完成
                if ($task->name == "新手答题") {
                    $take = $user->answers()->count() < 10;
                }

                if ($task->name == "首次提现奖励") {
                    $take = $user->wallet->total_withdraw_amount == 0;
                }
            }

            return $take;
        });

        return $assignments;
    }

    public function isDailyTask()
    {
        return $this->type == Task::DAILY_TASK;
    }

    public function isWeekTask()
    {
        return $this->type == Task::WEEK_TASK;
    }

    public function isTimeTask()
    {
        return $this->type == Task::TIME_TASK;
    }

    public function isNewUserTask()
    {
        return $this->type == Task::NEW_USER_TASK;
    }

    public function isCustomTask()
    {
        return $this->type == Task::CUSTOM_TASK;
    }

    public function isContributeTask()
    {
        return $this->type == Task::CONTRIBUTE_TASK;
    }

    public function getDailyStartTime()
    {
        $start_at       = $this->start_at;
        $start_date     = Carbon::parse($start_at);
        $start_date_day = $start_date->addDays($start_date->diffInDays(Carbon::tomorrow()));

        if (empty($start_at)) {
            $start_date_day = Carbon::today();
        }
        return $start_date_day;
    }

    public function getDailyEndTime()
    {
        $end_at       = $this->end_at;
        $end_date     = Carbon::parse($end_at);
        $end_date_day = $end_date->addDays($end_date->diffInDays(Carbon::tomorrow()));

        if (empty($end_at)) {
            $end_date_day = Carbon::tomorrow();
        }

        return $end_date_day;
    }

    public function getTaskContent()
    {

        $usertask_reward = $this->reward_info;
        if (empty($usertask_reward)) {
            return sprintf('%s完成。', $this->name);
        }
        $reward_content = '';
        if (array_get($usertask_reward, "gold")) {
            $reward_content = sprintf(" 金币+%s", array_get($usertask_reward, "gold"));
        }

        if (array_get($usertask_reward, "contribute")) {
            $reward_content = $reward_content . sprintf(" 贡献值+%s", array_get($usertask_reward, "contribute"));
        }

        $reward_info = sprintf('%s完成。奖励:', $this->name);

        return $reward_info . $reward_content;
    }

    public function getAssignment($user_id)
    {
        return Assignment::firstOrCreate([
            "task_id" => $this->id,
            "user_id" => $user_id,
        ]);
    }

    //检查并更新assignment的进度（current_count）和状态（status）
    public function checkTaskStatus($user, $assignment = null)
    {
        $task       = $this;
        $assignment = $assignment ?: $task->getAssignment($user->id);

        $reviewFlowName = data_get($task->review_flow, 'name');
        if ($assignment->status < Assignment::TASK_REACH || $reviewFlowName == '每日答题任务(聚合)') {
            if ($flow = $task->review_flow) {
                // 执行模版任务定义的检查方法s
                $checkoutFunctions = $flow->check_functions;
                if (is_array($checkoutFunctions)) {
                    foreach ($checkoutFunctions as $method) {

                        if (!method_exists($this, $method)) {
                            break;
                        }
                        //执行检查
                        //TODO: $result 支持决定是进入(已达成)未领取 还是 直接 进入已完成（可关闭）..
                        //1 $result['status']: false, true
                        $result = $this->$method($user, $task, $assignment);
                        if ($result['status']) {
                            //检查结果2：任务状态,已指派的更新为已完成
                            // 因为 有0 和 1 的状态 所以加一个 or
                            if ($assignment->status == Assignment::TASK_REVIEW || $assignment->status == Assignment::TASK_UNDONE) {
                                $assignment->status = Assignment::TASK_REACH;
                            }

                            $assignment->completed_at = now();
                        }

                        // 2. $result['current_count']: 当前进度
                        //检查结果1：任务进度
                        $assignment->current_count = Arr::get($result, 'current_count', 0);
                        if ($task->max_count > 0) {
                            $assignment->progress = $assignment->current_count / $task->max_count;
                        }

                        // 3. $result['is_over']: 是否直接结束任务
                        if (data_get($result, 'is_over') === true) {
                            $assignment->status = Assignment::TASK_DONE;
                        }

                        $assignment->save();
                    }
                }
            }
        }
        return $assignment->status;
    }

    public static function toastDiffTime($completed_at, $minutes)
    {
        $seconds = $minutes * 60;
        $diffmi  = Carbon::parse($completed_at)->diffInMinutes();
        $diffse  = Carbon::parse($completed_at)->diffInSeconds(now());
        if ($diffmi < $minutes) {
            if ($seconds - $diffse < 60 && $seconds - $diffse > 0) {
                $diffsecoend = 60 - $diffse;
                return '请' . $diffsecoend . '秒后来';
            } else {
                $diffminutes = $minutes - $diffmi;
                return '请' . $diffminutes . '分钟后来';
            }
        }
        return null;
    }

    public function saveDownloadImage($file)
    {
        if ($file) {
            $task_logo = 'task/task' . $this->id . '_' . time() . '.png';
            $cosDisk   = Storage::cloud();
            $cosDisk->put($task_logo, \file_get_contents($file->path()));

            return $task_logo;
        }
    }

    public function saveBackGroundImage($file)
    {
        if ($file) {
            $task_logo = 'task/background/task/' . $this->id . '_' . time() . '.png';
            $cosDisk   = Storage::cloud();
            $cosDisk->put($task_logo, \file_get_contents($file->path()));
            return $task_logo;
        }
    }

    /**
     * 获取喝水子任务列表
     *
     * @param $resolve
     * @return array[]
     */
    public static function getDrinkWaterSubTasks($resolve)
    {
        $results = [
            [
                'id'            => 1,
                'task_status'   => Assignment::TASK_UNDONE,
                'start_time'    => '9:00',
                'task_progress' => 0,
            ], [
                'id'            => 2,
                'task_status'   => Assignment::TASK_UNDONE,
                'start_time'    => '10:00',
                'task_progress' => 0,
            ], [
                'id'            => 3,
                'task_status'   => Assignment::TASK_UNDONE,
                'start_time'    => '11:00',
                'task_progress' => 0,
            ], [
                'id'            => 4,
                'task_status'   => Assignment::TASK_UNDONE,
                'start_time'    => '12:00',
                'task_progress' => 0,
            ], [
                'id'            => 5,
                'task_status'   => Assignment::TASK_UNDONE,
                'start_time'    => '13:00',
                'task_progress' => 0,
            ], [
                'id'            => 6,
                'task_status'   => Assignment::TASK_UNDONE,
                'start_time'    => '14:00',
                'task_progress' => 0,
            ], [
                'id'            => 7,
                'task_status'   => Assignment::TASK_UNDONE,
                'start_time'    => '15:00',
                'task_progress' => 0,
            ], [
                'id'            => 8,
                'task_status'   => Assignment::TASK_UNDONE,
                'start_time'    => '16:00',
                'task_progress' => 0,
            ],
        ];
        for ($position = 1; $position <= count($results); $position++) {
            $index = $position - 1;
            //补卡的状态
            $hour = Carbon::now()->hour;
            if ($position + 8 == $hour) {
                $results[$index]['task_status'] = Assignment::TASK_REVIEW;
            }
            if ($position + 8 < $hour) {
                $results[$index]['task_status'] = Assignment::TASK_FAILED;
            }

            //打卡完成状态
            if (is_array($resolve)) {
                if (in_array($position, $resolve)) {
                    $results[$index]['task_status']   = Assignment::TASK_DONE;
                    $results[$index]['task_progress'] = count($resolve) / 8;
                }
            }
        }
        return $results;
    }

    public static function completeTask($user, $task_id)
    {
        // $user
        $task = Task::find($task_id);
        throw_if(is_null($task), UserException::class, '任务完成失败!');
        // throw_if(!Str::contains($task->name, '试玩'), UserException::class, '该任务不是有效的试玩任务!');

        $pivot = Assignment::where([
            'user_id' => $user->id,
            'task_id' => $task->id,
        ])->first();

        if (!is_testing_env()) {
            throw_if(is_null($pivot), UserException::class, '请先领取任务!');
            throw_if($pivot->status > Assignment::TASK_DONE, UserException::class, '完成失败,请勿重复完成!');
            $pivot->fill(['status' => Assignment::TASK_REACH])->save();
        }

        return 1;
    }

    /**
     * 应用商店好评是工厂APP里假做审核的版本
     */
    public static function highPraise(User $user, Task $task, string $content): bool
    {
        $assignment = $task->getAssignment($user->id);

        if ($assignment->status >= Assignment::TASK_REACH) {
            throw new UserException('好评任务已经做过了哦~');
        }

        $assignment->status = Assignment::TASK_REVIEW; //提交回复后从未开始到审核中
        $assignment->save();

        //无需审核，1分钟后任务自动完成
        dispatch(new DelayRewaredTask($assignment->id));
        return 1;
    }
    /**
     * 应用商店好评-印象视频带审核版
     */
    public static function replyTaskWithCheck(User $user, Task $task, array $content)
    {
        $assignment = $task->getAssignment($user->id);
        if ($assignment->status >= Assignment::TASK_REACH) {
            throw new UserException('好评任务已经做过了哦~');
        }

        $assignment->status = Assignment::TASK_REVIEW; //提交回复后从未开始到审核中
        $assignment->save();
        $task->assignment = $assignment;

        $commentFeedback = Feedback::firstOrNew(
            [
                'user_id' => $user->id,
                'type'    => Feedback::COMMENT_TYPE,
            ]
        );
        $commentFeedback->content = Arr::get($content, 'info');
        $commentFeedback->contact = Arr::get($content, 'account');
        $commentFeedback->status  = Feedback::STATUS_PENDING;
        $commentFeedback->save();
        foreach ($content['images'] as $image) {
            $image      = Image::saveImage($image);
            $imageIds[] = $image->id;
        }
        $commentFeedback->images()->attach($imageIds);
        return $task;
    }

    /**
     * 答复任务，答赚里保存应用好评回复信息和截图地址到 content 这个json里
     */
    public static function replyTask(User $user, $task_id, $content)
    {
        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        if (!is_json($content)) {
            throw new UserException('提交有误,请稍后再试');
        }

        if ($assignment = $user->getAssignment($task_id)) {
            //防止重复提交,重复奖励
            if ($assignment->status == Assignment::TASK_REVIEW) {
                // throw new UserException('提交失败,请勿重复提交！');
                return 1;
            }

            $assignment->content = $content;
            $assignment->status  = Assignment::TASK_REVIEW; //提交回复后从未开始到审核中

            $assignment->save();

            //30秒后自动更改状态发放奖励
            dispatch(new DelayRewaredTask($assignment->id));
        }

        return 1;
    }

    public static function receiveTask($task_id)
    {
        $task = Task::where('id', $task_id)
            ->first();
        $user       = getUser();
        $assignment = Assignment::firstOrNew([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
        if (!$assignment->id) {
            $assignment->save();
            // Action::createAction('tasks', $task->id, $user->id);
        }
        return $task;
    }

    public static function rewardTask($task_id, $high = false)
    {
        $user       = getUser();
        $task       = Task::findOrFail($task_id);
        $assignment = Assignment::firstOrNew([
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);
        // 为了兼容前端 贡献任务 加一个条件 else //贡献任务可以多次领奖,所以不能直接更改状态为 Done
        if ($assignment->status == Assignment::TASK_REACH && $task->type != Task::CONTRIBUTE_TASK) {

            //聚合任务可以多次领取奖励
            $reviewFlow = $task->review_flow;
            if (!is_null($reviewFlow) && $reviewFlow->name == '每日答题任务(聚合)') {
                $is_done = $assignment->current_count >= $task->max_count;
                if ($is_done) {
                    $assignment->status = Assignment::TASK_DONE;
                } else {
                    $assignment->status = Assignment::TASK_REVIEW;
                }
            } else {
                $assignment->status = Assignment::TASK_DONE;
            }

            $assignment->save();
            //领取奖励
            Task::reward($user, $task, $assignment, $high);
        } else if ($task->type == Task::CONTRIBUTE_TASK) {
            //如果贡献任务有完成次数
            if ($task->max_count > 0) {
                $is_done = $assignment->current_count >= $task->max_count;
                if ($is_done) {
                    $assignment->status = Assignment::TASK_DONE;
                    $assignment->save();
                }
            }
            Task::reward($user, $task, $assignment, $high);
        } else if ($task->type == Task::DAILY_TASK && env('APP_NAME') == 'ablm') {
            //如果贡献任务有完成次数
            if ($task->max_count > 0) {
                $is_done = $assignment->current_count >= $task->max_count;
                if ($is_done) {
                    $assignment->status = Assignment::TASK_DONE;
                    $assignment->save();
                }
            }
            Task::reward($user, $task, $assignment, $high);
        }

        //没有任何奖励,要抛异常给前端,否则APP会崩溃
        $attributes      = ['gold', 'gold_high', 'contribute', 'contribute_high', 'ticket', 'ticket_high'];
        $isError         = false;
        $nullRewardCount = 0;
        foreach ($attributes as $attr) {
            if (!Arr::get($task->reward, $attr, 0)) {
                $nullRewardCount++;
            }
        }
        throw_if(count($attributes) == $nullRewardCount, UserException::class, '领取失败,请勿重复领取哦!');

        return $task;
    }

    public static function reward($user, &$task, $assignment, $high)
    {
        // 判断奖励是否存在只需要判断 普通额度的奖励即可, 低额不一定有高额,但高额一定会有低额
        // 金币奖励
        $rewardInfo = $task->rewardInfo;
        $gold       = $high ? $rewardInfo['gold_high'] : $rewardInfo['gold'] ?? 0;

        //精力奖励
        $ticket = $high ? $rewardInfo['ticket_high'] : $rewardInfo['ticket'] ?? 0;

        //贡献奖励
        $contribute = $high ? $rewardInfo['contribute_high'] : $rewardInfo['contribute'] ?? 0;

        if (isset($rewardInfo['gold']) && $gold > 0) {
            $remark = sprintf('%s奖励', $task->name);
            Gold::makeIncome($user, $gold, $remark);
        }

        //精力奖励
        if (isset($rewardInfo['ticket']) && $ticket > 0) {

            $user->ticket = $user->ticket + $ticket;
            $user->save();
        }

        //贡献奖励
        if (isset($rewardInfo['contribute']) && $contribute > 0) {

            Contribute::rewardAssignmentContribute($user, $assignment, $contribute);
        }

        //聚合任务奖励
        $reviewFlow = $task->review_flow;
        if (!is_null($reviewFlow) && $reviewFlow->name == '每日答题任务(聚合)') {
            $resolve = $assignment->resolve;
            //目标奖励金额
            $rewardTicket = Arr::get($resolve, 'reward_ticket', 0);
            //已领取奖励
            $receiveTicket = Arr::get($resolve, 'receive_ticket', 0);
            $surplusTicket = $rewardTicket - $receiveTicket;
            if ($surplusTicket > 0) {
                //更新领取精力点数量
                $resolve['receive_ticket'] = $receiveTicket + $surplusTicket;
                $assignment->resolve       = $resolve;
                $assignment->save();

                //更新精力点
                $user->ticket += $surplusTicket;
                $user->save();

                //临时改变一下task 返回的精力点数据 给前端展示
                $reward_info           = $task->reward_info;
                $reward_info['ticket'] = $surplusTicket;
                $task->reward          = $reward_info;
            }
        }
    }

    /**
     * 刷新任务完成进度（每日任务推荐使用）
     */
    public static function refreshTask($user, $taskName)
    {
        $tasks = $user->getCommonTasks($taskName);
        foreach ($tasks as $task) {
            $assignment = $user->tasks()->where('task_id', $task->id)->first();
            if ($assignment) {
                $assignment_pivot = $assignment->pivot;
                if ($assignment_pivot->current_count < $task->max_count) {
                    $assignment_pivot->update(["current_count" => DB::raw("current_count+1"), "progress" => DB::raw("current_count/" . $task->max_count)]); //次数加1
                }
                $task->checkTaskStatus($user);
            }
        }
    }

    /**
     * 检查发布问题的数量
     */
    public function checkIssueCount($user, $task, $assignment)
    {
        $count = $assignment->current_count;
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count,
        ];
    }

    /**
     * 检查回答问题的数量
     */
    public function checkSolutionCount($user, $task, $assignment)
    {
        $count = $assignment->current_count;
        return [
            'status'        => $count >= $task->max_count,
            'current_count' => $count,
        ];
    }

    public static function getActions()
    {
        return [
            self::LIKE_ACTION      => '点赞',
            self::COMMENT_ACTION   => '评论',
            self::VISIT_ACTION     => '浏览',
            self::FAVORABLE_ACTION => '收藏',
        ];
    }
    public static function getActionClasses()
    {
        return [
            self::POST       => '动态',
            self::USER       => '用户',
            self::COLLECTION => '集合',
            self::MOVIE      => '电影',
        ];
    }


}

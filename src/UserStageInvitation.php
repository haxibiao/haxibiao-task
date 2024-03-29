<?php

namespace Haxibiao\Task;

use App\User;
use Haxibiao\Wallet\Wallet;
use Haxibiao\Task\StageInvitation;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Traits\ModelHelpers;
use Haxibiao\Breeze\Exceptions\UserException;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserStageInvitation extends Pivot
{
    use ModelHelpers;

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'stage_id',
    ];

    private $cacheable = [
        'wallet'          => 'inviteWallet',
        'invite_progress' => 'inviteProgress',
    ];

    const MIN_STAGE_ID = 1;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stage()
    {
        return $this->belongsTo(StageInvitation::class);
    }

    public function inviteWallet()
    {
        $user = $this->user;
        // 拿到邀请钱包
        $wallet = $user->invitationWallets()->first();
        if (is_null($wallet)) {
            $wallet = Wallet::findOrCreate($user->id, Wallet::INVITATION_TYPE);
        }

        return $wallet;
    }

    public function inviteProgress()
    {
        $inviteWallet       = $this->wallet;
        $stage              = $this->stage;
        $progress           = 0;
        $totalIncome        = $inviteWallet->totalIncomeWithInvitation();
        $historyStageAmount = StageInvitation::where('id', '<', $this->stage_id)->sum('amount');
        $currentStageIncome = bcsub($totalIncome, $historyStageAmount, 4);
        if ($currentStageIncome > 0) {
            $progress = bcdiv($currentStageIncome, $stage->amount, 2);
            $progress = $progress >= 1 ? 1 : $progress;
        }

        return $progress * 100;
    }

    public static function findOrCreate($userId)
    {
        return UserStageInvitation::firstOrCreate(['user_id' => $userId], ['stage_id' => UserStageInvitation::MIN_STAGE_ID]);
    }

    public function levelUp($wallet)
    {
        $isLevelUp = false;
        // 邀请进度满了
        if ($this->invite_progress >= 100) {
            $nextStageId = data_get(StageInvitation::select('id')->where('id', '>', $this->stage_id)->first(), 'id');
            if ($nextStageId) {
                $this->stage_id = $nextStageId;
                $this->save();
                $this->load('stage');
                $isLevelUp = true;
            }
        }

        return $isLevelUp;
    }

    public function resolveUserStageInvitation($root, $args, $context, ResolveInfo $info)
    {
        $userId = data_get(User::select('id')->find($args['user_id']), 'id');
        throw_if(is_null($userId), UserException::class, '查询失败,用户信息不存在!');

        $stageInvitation = UserStageInvitation::findOrCreate($userId);
        $stageInvitation->load(['user', 'stage']);
        $user = $stageInvitation->user;
        $user->inviteReward();

        if (!is_null($stageInvitation)) {
            $user   = $stageInvitation->user;
            $wallet = $stageInvitation->wallet;
            $stageInvitation->levelUp($wallet);
            $todayIncome = $wallet->todayIncome();
            $data        = [
                'id'                               => $stageInvitation->id,
                'invite_code'                      => $user->invite_code,
                'invite_slogan'                    => $user->invite_slogan,
                'wallet'                           => $wallet,
                'stage'                            => $stageInvitation->stage,
                'invite_progress'                  => $stageInvitation->invite_progress,
                'invitations_success_count'        => $user->invitationsSuccessCount,
                'self_is_invited'                  => $user->selfIsInvited,
                'today_invitation_income'          => number_format($todayIncome, 2),
                'today_apprentice_bonus'           => number_format($wallet->todayApprenticeBonus(), 2),
                'today_secondary_apprentice_bonus' => number_format($wallet->todaySecondApprenticeBonus(), 2),
            ];
        }

        return $data;
    }
}

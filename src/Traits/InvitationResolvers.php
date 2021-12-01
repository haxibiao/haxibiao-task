<?php

namespace Haxibiao\Task\Traits;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Task\Invitation;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait InvitationResolvers
{
    //邀请用户
    public function resolveInviteUser($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        app_track_event("邀请", "邀请用户对接");
        $user    = User::find($args['user_id']);
        $account = data_get($args, 'account');
        if (is_null($user)) {
            throw new UserException('邀请人不存在,请重试!');
        }
        return Invitation::connect($user, $account);
    }

    //邀请列表
    public function resolveInvitationUsers($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (isset($args['user_id'])) {
            $user = User::find($args['user_id']);
        } else {
            $user = getUser();
        }

        if (is_null($user)) {
            throw new UserException('获取失败,用户不存在!');
        }

        return Invitation::invitations($user, $args);
    }

    //邀请奖励列表
    public function resolveInvitationRewards($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return Invitation::invitationRewards(data_get($args, 'limit', null));
    }

    //是否邀请用户
    public function resolveIsInviteUser($root, $args, $context, ResolveInfo $info)
    {
        return Invitation::isInviteUser($args['account']);
    }

    //绑定邀请，邀请码/口令
    public function resolveBindInvitation($root, $args, $context, ResolveInfo $info)
    {
        $inviteCode = $args['invite_code'];

        return Invitation::inviteCodeBind(getUser(), $inviteCode);
    }

    public static function resolveInvitations($root, $args, $context, ResolveInfo $info)
    {
        $status = $args['status'];
        $user   = getUser();

        if ($status == 'ACTIVE') {
            $qb = $user->invitations()->active();
        } else if ($status == 'INACTIVE') {
            $qb = $user->invitations()->inactive();
        } else {
            $qb = $user->secondaryApprentices();
        }

        $qb->with('transaction');

        return $qb;
    }

    public function resolveRedeemInviteCode($rootValue, $args, $context, $resolveInfo)
    {
        // 校验类型
        $invitee        = getUser();
        $inviteCode     = data_get($args, 'invite_code');
        $inviteCodeType = data_get($args, 'invite_code_type');
        if ($inviteCodeType == 'USER_ID') {

            // 判断模型是否找到
            $inviter = User::find($inviteCode);
            throw_if(blank($inviter), new UserException('邀请号格式有问题!'));
            throw_if($invitee->id == $inviter->id, new UserException('不能绑定自己的邀请号!'));

            $hasInvitation = static::withoutGlobalScope('hasInvitedUser')->where('be_inviter_id', $invitee->id)->exists();
            throw_if($hasInvitation, UserException::class, '绑定失败,您的账号已绑定过邀请!');

            return static::withoutEvents(function () use ($inviter, $invitee) {
                return static::create([
                    'user_id'       => $inviter->id,
                    'invited_in'    => now(),
                    'be_inviter_id' => $invitee->id,
                ]);
            });
        }
    }

    public function resolveInvitees($rootValue, $args, $context, $resolveInfo)
    {
        $user_id     = data_get($args, 'inviter_id');
        $perPage     = data_get($args, 'first');
        $currentPage = data_get($args, 'page');
        $inviter     = User::findOrFail($user_id);

        $inviteeIDs = static::withoutGlobalScope('hasInvitedUser')->where('user_id', $inviter->id)->get()->pluck('be_inviter_id');
        $qb         = User::whereIn('id', $inviteeIDs);

        $total   = $qb->count();
        $meetups = $qb->skip(($currentPage * $perPage) - $perPage)
            ->take($perPage)
            ->get();
        return new \Illuminate\Pagination\LengthAwarePaginator($meetups, $total, $perPage, $currentPage);
    }
}
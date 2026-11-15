<?php

namespace App\Services;

use App\Models\RewardProgram;
use App\Models\RewardTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Points balances and staff adjustments. Balances ignore expired awards.
 */
class RewardLedger
{
    public function __construct(private ActivityRecorder $activity) {}

    public function balance(User $user, RewardProgram $program): int
    {
        return (int) RewardTransaction::query()
            ->where('user_id', $user->getKey())->where('reward_program_id', $program->getKey())
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->sum('points');
    }

    /**
     * @throws ValidationException when the programme is not live or the balance would go negative
     */
    public function adjust(User $customer, RewardProgram $program, int $points, string $reason, User $actor): RewardTransaction
    {
        if (! RewardProgram::query()->live()->whereKey($program->getKey())->exists()) {
            throw ValidationException::withMessages(['reward_program_id' => 'Points can only be changed in a published, active programme.']);
        }
        if ($points === 0) {
            throw ValidationException::withMessages(['points' => 'Enter a positive or negative number of points.']);
        }

        return DB::transaction(function () use ($customer, $program, $points, $reason, $actor): RewardTransaction {
            if ($points < 0 && $this->balance($customer, $program) + $points < 0) {
                throw ValidationException::withMessages(['points' => 'The customer does not have enough points.']);
            }

            $transaction = RewardTransaction::query()->create([
                'user_id' => $customer->getKey(),
                'reward_program_id' => $program->getKey(),
                'points' => $points,
                'reason' => $reason,
                'expires_at' => $points > 0 && $program->points_expire_after_days ? now()->addDays($program->points_expire_after_days) : null,
                'created_by_user_id' => $actor->getKey(),
            ]);
            $this->activity->record($actor, 'reward.adjusted', $transaction, ['program' => $program->slug, 'points' => $points, 'customer_id' => $customer->getKey()]);

            return $transaction;
        });
    }
}

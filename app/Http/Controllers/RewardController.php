<?php

namespace App\Http\Controllers;

use App\Models\RewardProgram;
use App\Models\RewardTransaction;
use App\Services\RewardLedger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function __invoke(Request $request, RewardLedger $ledger): View
    {
        $programs = RewardProgram::query()->live()->orderBy('name')->get();
        $user = $request->user();

        return view('rewards.index', [
            'programs' => $programs,
            'balances' => $user ? $programs->mapWithKeys(fn (RewardProgram $program): array => [$program->id => $ledger->balance($user, $program)]) : collect(),
            'history' => $user && $programs->isNotEmpty()
                ? RewardTransaction::query()->where('user_id', $user->getKey())->whereIn('reward_program_id', $programs->pluck('id'))->latest('id')->limit(50)->get(['points', 'reason', 'expires_at', 'created_at', 'reward_program_id'])
                : collect(),
        ]);
    }
}

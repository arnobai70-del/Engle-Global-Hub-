<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use App\Services\ActivityRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthTokenController extends Controller
{
    public function store(Request $request, ActivityRecorder $activity): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::query()->where('email', strtolower($data['email']))->first();
        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages(['email' => 'Verify your email address on the website before using the app.']);
        }

        [$token, $plain] = ApiToken::issue($user, strip_tags($data['device_name']));
        $activity->record($user, 'api.token_issued', $token, ['device' => $token->name]);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $plain,
            'expires_at' => $token->expires_at->toAtomString(),
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $plain = $request->bearerToken();
        if (is_string($plain) && $plain !== '') {
            ApiToken::query()->where('token_hash', hash('sha256', $plain))->where('user_id', $request->user()->getKey())->delete();
        }

        return response()->json(null, 204);
    }
}

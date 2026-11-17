<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * Every self-registered account is a customer. The chosen account type only
     * decides where the visitor goes next; an agent still has to apply and be
     * approved before the Agent Portal opens.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'account_type' => ['required', Rule::in(array_keys(User::ACCOUNT_TYPES))],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ], [
            'account_type.required' => __('Choose the kind of account you need.'),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'account_type' => $input['account_type'],
        ]);

        if (Role::query()->where('name', 'customer')->where('guard_name', 'web')->exists()) {
            $user->assignRole('customer');
        }

        return $user;
    }
}

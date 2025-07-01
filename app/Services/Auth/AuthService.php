<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthService
{
    /**
     * @throws AuthenticationException
     */
    public function attempt(array $payload): array
    {
        $validated = Validator::validate($payload, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_id' => ['required', 'string', 'max:190'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (blank($user) || ! Hash::check($validated['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $tokenResult = $user->createToken($validated['device_id']);

        return [
            'token_type' => 'Bearer',
            'expires_in' => now()->diffInSeconds($tokenResult->getToken()->expires_at),
            'access_token' => $tokenResult->accessToken,
        ];
    }
}

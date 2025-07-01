<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        protected readonly AuthService $service,
    ) {}

    public function store(Request $request)
    {
        try {
            return response()->json([
                'message' => __('api.auth.attempt.success'),
                'data' => $this->service->attempt($request->all()),
            ]);
        } catch (Throwable $exception) {
            $status = 422;

            if ($exception instanceof AuthenticationException) {
                $status = 401;
            }

            return response()->json([
                'message' => __('api.auth.attempt.failed'),
                'error' => $exception->getMessage(),
            ], $status);
        }
    }

    public function destroy()
    {
        $user = auth()->user();
        $user->currentAccessToken()->revoke();

        return response()->json([
            'message' => __('api.auth.logout.success'),
        ]);
    }
}

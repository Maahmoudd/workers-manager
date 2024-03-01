<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\WorkerStoreRequest;
use App\Services\WorkerService\WorkerAuthService\WorkerLoginService;
use App\Services\WorkerService\WorkerAuthService\WorkerRegisterService;

class WorkerAuthController extends Controller
{

    protected $loginService;
    protected $registerService;
    public function __construct(WorkerLoginService $loginService, WorkerRegisterService $registerService)
    {
        $this->loginService = $loginService;
        $this->registerService = $registerService;
        $this->middleware('auth:worker', ['except' => ['login', 'register', 'verify']]);
    }

    public function login(LoginRequest $request)
    {
        return $this->loginService->login($request);
    }

    public function register(WorkerStoreRequest $request)
    {
        return $this->registerService->register($request);
    }
    function verify($token)
    {
        $worker = Worker::whereVerificationToken($token)->first();
        if (!$worker) {
            return response()->json([
                "message" => "this token is invalid"
            ]);
        }
        $worker->verification_token = null;
        $worker->verified_at = now();
        $worker->save();
        return response()->json([
            "message" => "your account has been verified"
        ]);
    }

    public function logout()
    {
        auth()->guard('worker')->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }
}

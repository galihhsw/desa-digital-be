<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginStoreRequest;
use App\Http\Requests\UserStoreRequest;
use App\Interfaces\AuthRepositoryInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(UserStoreRequest $request)
    {
        $data = $request->validated();
        $result = $this->authRepository->register($data);

        return response()->json($result, 200);
    }

    public function login(LoginStoreRequest $request)
    {
        $request = $request->validated();
        $result = $this->authRepository->login($request);

        return response()->json($result, 200);
    }

    public function logOut()
    {
        $this->authRepository->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout Success!'
        ], 200);
    }

    public function me()
    {
        $userData = $this->authRepository->me();

        if (!$userData) {
            return response()->json([
                'message' => 'You are not logged in!'
            ], 401);
        }

        return response()->json($userData, 200);
    }
}

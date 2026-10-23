<?php

namespace App\Repositories;

use App\Helpers\ResponseHelper;
use App\Models\User;
use App\Http\Resources\HeadOfFamilyResource;
use App\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class AuthRepository implements AuthRepositoryInterface
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->assignRole('head-of-family');

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'message' => 'Register Success!'
        ];
    }

    public function login(array $data)
    {if (!Auth::guard('web')->attempt($data)) {
        throw new \Exception('Unauthorized');
    }

    // Ambil dari guard 'web'
    $user = Auth::guard('web')->user();

    if (!$user) {
        throw new \Exception('User tidak ditemukan');
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return [
        'user'  => $user,
        'token' => $token,
        'message' => 'Login Success!'
    ];
    }

    public function logout()
    {
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return true;
    }

    public function me()
    {
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        if ($user) {
            $user->load('roles.permissions', 'headOfFamily');

            $permissions = $user->getAllPermissions()->pluck('name');
            $role = $user->roles->first()?->name;

            return [
                'message' => 'User Data',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'permissions' => $permissions,
                    'role' => $role,
                    'head_of_family' => $user->headOfFamily ? new HeadOfFamilyResource($user->headOfFamily) : null
                ],
            ];
        }
        
        return null;
    }
}
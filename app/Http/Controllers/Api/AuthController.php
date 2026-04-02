<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => User::ROLE_EMPLOYEE,
            'jabatan'  => $request->jabatan,
            'divisi'   => $request->divisi,
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->createdResponse(
            [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'role'    => $user->role,
                    'jabatan' => $user->jabatan,
                    'divisi'  => $user->divisi,
                ],
            ],
            'Registrasi berhasil.'
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Email atau password salah.', [], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('api_token')->plainTextToken;

        return $this->successResponse(
            [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'role'    => $user->role,
                    'jabatan' => $user->jabatan,
                    'divisi'  => $user->divisi,
                ],
            ],
            'Login berhasil.'
        );
    }
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout success'
        ]);
    }
}

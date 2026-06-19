<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Build store list based on role
        if ($user->role === 'owner') {
            $stores = $user->stores()->select('id', 'name', 'code', 'status')->get();
        } else {
            $stores = $user->assignedStore()->select('stores.id', 'stores.name', 'stores.code', 'stores.status')->get();
        }

        return $this->successResponse([
            'user'          => $user,
            'stores'        => $stores,
            'current_store' => $stores->first(),
            'token'         => $token,
        ], 'Login successful');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:owner,manager,cashier',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'User registered successfully', 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Delete old token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Build store list based on role
        if ($user->role === 'owner') {
            $stores = $user->stores()->select('id', 'name', 'code', 'status')->get();
        } else {
            $stores = $user->assignedStore()->select('stores.id', 'stores.name', 'stores.code', 'stores.status')->get();
        }

        return $this->successResponse([
            'user'          => $user,
            'stores'        => $stores,
            'current_store' => $stores->first(),
            'token'         => $token,
        ], 'Token refreshed successfully');
    }

    public function me(Request $request)
    {
        return $this->successResponse($request->user());
    }
}

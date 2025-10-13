<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // 🔹 Register Client
    public function registerClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|max:55',
            'email'    => 'required|email|unique:users|min:3|max:60',
            'password' => 'required|min:6|confirmed',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'phone_number' => 'nullable|string|max:13',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'client',
            'logo' => $request->hasFile('logo') ? $request->file('logo')->store('images/profiles/logos', 'public') : null,
            'image' => $request->hasFile('image') ? $request->file('image')->store('images/profiles/thumbnails', 'public') : null,
            'phone_number' => $request->phone_number,
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'token' => $token,
            'user'  => $user
        ], 201);
    }

    /**
     * Display the authenticated user's data.
     */
    public function edit($id)
    {
        if ($id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        return response()->json([
            'user' => $user,
        ], 200);
    }

    /**
     * Update the authenticated user's data.
     */
    public function update(Request $request, $id)
    {
        if ($id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|max:55',
            'password' => 'sometimes|min:6|confirmed',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Prepare data for update
        $data = $request->only([
            'name', 'phone_number'
        ]);

        // Handle password
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // If a new image is uploaded Delete old file if exists
        // Handle file uploads and deletions
        if ($request->hasFile('logo')) {
            if ($user->logo) {
                Storage::disk('public')->delete($user->logo);
            }
            $data['logo'] = $request->file('logo')->store('images/profiles/logos', 'public');
        } elseif ($request->has('logo') && $request->logo === null) {
            if ($user->logo) {
                Storage::disk('public')->delete($user->logo);
            }
            $data['logo'] = null;
        }

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            $data['image'] = $request->file('image')->store('images/profiles/thumbnails', 'public');
        } elseif ($request->has('image') && $request->image === null) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            $data['image'] = null;
        }

        // Update the user
        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }

    // 🔹 Login
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user
        ]);
    }

    // 🔹 Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully'
        ]);
    }
}

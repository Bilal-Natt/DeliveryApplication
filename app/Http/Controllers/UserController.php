<?php

namespace App\Http\Controllers;

use App\Models\User;
//use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function formatPhone($phone)
    {
        if (strlen($phone) == 13) {
            $phone = "09" . substr($phone, 5);
        }
        return $phone;
    }
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => [
                'required',
                'regex:/^(?:\+9639\d{8}|09\d{8})$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8', // Minimum password length
                'confirmed', // Password confirmation
                'regex:/[a-z]/', // At least one lowercase letter
                'regex:/[A-Z]/', // At least one uppercase letter
                'regex:/[0-9]/', // At least one numeric digit
                'regex:/[@$!%*?&]/', // At least one special character
            ],
        ]);

        $phone = $this->formatPhone($request->phone);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'role_id' => 2, // Default role: User
        ]);
        return response()->json(
            [
                "message" => "User registered successfully",
                "user" => $user
            ],
            201
        );
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => [
                'required',
                'regex:/^(?:\+9639\d{8}|09\d{8})$/',
            ],
            'password' => 'required|string',
        ]);

        $phone = $this->formatPhone($request->phone);

        $user = User::where('phone', $phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "message" => "Invalid phone or password",
            ], 401);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            "message" => "Login successful",
            "token" => $token,
            "user" => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        // $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "Logged out successfully",
        ], 200);
    }

    /**
     * this  function is to update the user profile
     */
    public function updateUser(Request $request)
    {
        $user = User::findOrFail($request->id);
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'password' => [
                'sometimes',
                'string',
                'min:8', // Minimum password length
                'confirmed', // Password confirmation
                'regex:/[a-z]/', // At least one lowercase letter
                'regex:/[A-Z]/', // At least one uppercase letter
                'regex:/[0-9]/', // At least one numeric digit
                'regex:/[@$!%*?&]/'
            ],
            'address' => 'sometimes|url|max:255',
            'image_path' => 'sometimes|string|max:255'
        ]);

        $user->update($validatedData);
        return response()->json(['message' => 'the info has been updated'], 200);
    }


}

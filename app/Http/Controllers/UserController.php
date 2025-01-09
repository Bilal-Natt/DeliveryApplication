<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shop;
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
            'role_id' => 2,
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

    public function verify(Request $request)
    {
        if($request->code == 192834){
            return response()->json([
                "message" => "Verified",
            ]);
        }
        return response()->json([
            "message" => "Invalid code , Try again!"
        ]);
    }

    public function logout(Request $request)
    {
        // $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "Logged out successfully",
        ], 200);
    }
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);
        $imageName = time() . '_'.auth()->user()->phone. '.' . request()->image->getClientOriginalExtension();
        request()->image->move(base_path('storage/app/public/images'), $imageName);
        return response()->json([
            "message" => "Image uploaded successfully",
            "image" => $imageName
        ]);

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
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/'
            ],
            'address' => 'sometimes|url|max:255',
        ]);
        $user->update($validatedData);

        $user->image_path = public_path('images').'/'.$request->imageName;
        $user->save();


        return response()->json(['message' => 'the info has been updated'], 200);
    }
    public function getImage(Request $request)
    {
        $user = auth()->user();
        return response()->json([
            "image" => $user->image_path
        ]);
    }

    public function getFavoritesProducts(Request $request)
    {
        $products = User::findOrFail($request->user_id)->products->map(function ($product) {
            return [
                'name' => $product->name,
                'price' => $product->price,
                'shop' => Shop::where('id', $product->shop_id)->pluck('name')->first()
            ];
        });
        return response()->json($products);
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\CheckCurrentPassword;
use App\Rules\CheckNewPasswordNotSameInDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        //validation 
        $request->validate([
            'name' => ['required'],
            'email' => ['required','unique:users,email'],
            'password' => ['required', 'min:6','confirmed' ],
        ]);
        //creating user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        // create token
        $token = $user->createToken("default")->plainTextToken;

        // send success message
        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ]);
    }

    public function login(Request $request){
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        //find user with email and check if password is correct
        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password,$user->password)){
            return response()->json([
                'success' => false,
                'message' => 'Incorrect email or password'
            ]);
        }

        // delete any other existing token for the user
        $user->tokens()->delete();

        // create new token for user
        $token = $user->createToken("Login")->plainTextToken;

        // return token
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
            ]
        ]);
    }

    public function logout(Request $request) {
        auth("sanctum")->user()->tokens()->delete();
        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully'
        ]);
    }

    public function updateMyPassword(Request $request) {
        $request->validate([
            'current_password' => ['required', new CheckCurrentPassword()],
            'new_password' => ['required','min:6',new CheckNewPasswordNotSameInDatabase],
        ]);

        $user = auth('sanctum')->user();

        // if(Hash::check($request->new_password, $user->password)){
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'New password cannot be the same as your current password. pick a new password',
        //     ]);
        // }

        $user->update(['password' => Hash::make($request->new_password)]);

        // delete any other existing token for the user
        $user->tokens()->delete();

        // create new token for user
        $token = $user->createToken("Login")->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
            'data' => ['token'=>$token]
        ]);
    }

    public function updateUser(Request $request) {
        $request->validate([
            'name' => ['required','min:3'],
            'email' => ['required']
        ]);

        $user = auth('sanctum')->user();

        $user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Name updated successfully'
        ]);
    }
}

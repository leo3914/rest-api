<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function create()
    {
        $validator = Validator::make(request()->all(), [
            'name' => ['required', 'min:5', 'max:40'],
            'email' => ['required', 'email', 'email:rfc,dns'],
            'phone' => ['required', 'min:9'],
            'password' => ['required', 'min:6', 'max:30'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Unprocessable Content',
                'error' => $validator->errors(),
            ], 422);
        }

        $exitedEmail = User::where('email', request('email'))->exists();

        if ($exitedEmail) {
            return response()->json([
                'message' => "Email already exists."
            ]);
        }

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'phone' => request('phone'),
            'password' => Hash::make(request('password')),
        ]);

        return response()->json([
            'message' => "User account successfully created.",
        ], 201);
    }

    public function login()
    {
        $validator = Validator::make(request()->all(), [
            'email' => ['required', 'email', 'email:rfc,dns'],
            'password' => ['required', 'min:6', 'max:30'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Unprocessable Content',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', request('email'))->first();

        if (!$user) {
            return response()->json([
                'message' => "Email doesn't exists."
            ], 422);
        }

        $isPasswordCorrect = Hash::check(request('password'), $user->password);

        if (!$isPasswordCorrect) {
            return response()->json([
                'message' => "Password Incorrect."
            ], 422);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'message' => "Login Success.",
            'token' => $token,
        ], 200);
    }

}

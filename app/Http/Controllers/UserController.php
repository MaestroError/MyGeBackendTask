<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Registers new user and returns login token
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed ',
        ]);
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            "password" => bcrypt($fields['password'])
        ]);

        $token = $user->createToken('login_token')->plainTextToken;

        $response = [
            "user" => $user,
            "token" => $token
        ];
        
        return response($response, 201)->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Returns login token for user
     *
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string ',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                "message" => "Bad creds"
            ], 404);
        };

        $token = $user->createToken('login_token')->plainTextToken;

        $response = [
            "user" => $user,
            "token" => $token
        ];
        
        return response($response, 200);
    }
    
    /**
     * Deletes tokens, so logs out an User
     *
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request): Response
    {
        auth()->user()->tokens()->delete();
        return response([
            'message' => 'logged out'
        ], 200);
    }
}

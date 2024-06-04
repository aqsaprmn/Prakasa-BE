<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */

    public function register(Request $request)
    {
        $request->validate([
            "name" => "required|string",
            "email" => "required|string|email|unique:users",
            "phone" => "required|numeric|unique:users",
            "password" => "required|confirmed",
            "role" => "required|string"
        ]);

        try {
            $data = [
                "uuid" => Uuid::uuid(),
                "name" => $request->get("name"),
                "email" => $request->get("email"),
                "phone" => $request->get("phone"),
                "role" => $request->get("role"),
                "password" => bcrypt($request->get("password"))
            ];

            $registration = User::create($data);

            return response()->json([
                "success" => true,
                "message" => "Registration success",
                "data" => ["user" => [
                    "uuid" => $registration->uuid
                ]]
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "data" => ["error" => $e]
            ], 400);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // $user = User::where("email", $credentials["email"])->first();

        // $token = $user->createToken('mytoken')->accessToken;

        $request = Request::create(env('APP_URL') . '/oauth/token', 'POST', [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
            'username' => $credentials["email"],
            'password' => $credentials["password"],
            'scope' => '',
        ]);

        $result = app()->handle($request);
        $response = json_decode($result->getContent(), true);

        return $this->respondWithToken($response);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth()->logout();

            return response()->json([
                "success" => true,
                'message' => 'Successfully logged out',
                "data" => []
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "data" => ["error" => $e]
            ], 400);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        $token = $request->get("refresh_token");

        $request = Request::create(env('APP_URL') . '/oauth/token', 'POST', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token,
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
            'scope' => '',
        ]);

        $result = app()->handle($request);
        $response = json_decode($result->getContent(), true);

        return $this->respondWithToken($response);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            "success" => true,
            "message" => "Login success",
            "data" => [
                "detail" => auth()->user(),
                'access_token' => $token["access_token"],
                'refresh_token' => $token["refresh_token"],
                'token_type' => $token["token_type"],
                'expires_in' => $token["expires_in"]
            ]
        ], 200);
    }
}

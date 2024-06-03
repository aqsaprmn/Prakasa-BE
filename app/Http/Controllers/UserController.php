<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::where('role', '!=', "SUPER-ADMIN")->orderBy("created_at", "desc")->get()->toArray();

            $users = Arr::map($users, function ($value) {
                return [
                    "user" => $value
                ];
            });

            return response()->json([
                "success" => true,
                "message" => "Show list users success",
                "data" => $users
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string",
            "email" => "required|string|email|unique:users",
            "phone" => "required|string",
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
                "message" => "Create user success",
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
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $user = User::where("uuid", $uuid)->first();

        if (!$user) {
            return response()->json([
                "success" => true,
                "message" => "Show detail user failed",
                "data" => [
                    "user" => "User not found",
                ]
            ], 400);
        }

        try {
            return response()->json([
                "success" => true,
                "message" => "Show detail user success",
                "data" => [
                    "user" => $user,
                ]
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "data" => ["error" => $e->getMessage()]
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $user = User::where("uuid", $uuid)->first();

        if (!$user) {
            return response()->json([
                "success" => true,
                "message" => "Update user failed",
                "data" => [
                    "user" => "User not found",
                ]
            ], 400);
        }

        $request->validate([
            "name" => "required|string",
            "email" => "required|string|email|unique:users,email," . $user->id,
            "phone" => "required|string",
            "role" => "required|string"
        ]);

        try {
            $data = [
                "name" => $request->get("name"),
                "email" => $request->get("email"),
                "phone" => $request->get("phone"),
                "role" => $request->get("role"),
            ];

            $user->update($data);

            return response()->json([
                "success" => true,
                "message" => "Edit user success",
                "data" => [
                    "user" => [
                        "uuid" => $user->uuid
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "data" => ["error" => $e->getMessage()]
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $user = User::where("uuid", $uuid)->first();

        if (!$user) {
            return response()->json([
                "success" => true,
                "message" => "Delete user failed",
                "data" => [
                    "user" => "User not found",
                ]
            ], 400);
        }

        try {
            $user->delete();

            return response()->json([
                "success" => true,
                "message" => "Delete user success",
                "data" => [
                    "user" => [
                        "uuid" => $user->uuid
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "data" => ["error" => $e->getMessage()]
            ], 400);
        }
    }
}

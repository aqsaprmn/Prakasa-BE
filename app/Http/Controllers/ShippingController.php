<?php

namespace App\Http\Controllers;

use App\Models\Shipping;
use App\Http\Controllers\Controller;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ShippingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $shippings = Shipping::orderBy("created_at", "desc")->get()->toArray();

            if ($request->has('user_uuid')) {
                $user_uuid = $request->input('user_uuid');
                $shippings = Shipping::where("user_uuid",  $user_uuid)
                    ->orderBy('created_at', 'desc')->get()->toArray();
            }

            $shippings = Arr::map($shippings, function ($value) {
                return [
                    "shipping" => $value
                ];
            });

            return response()->json([
                "success" => true,
                "message" => "Show list shipping success",
                "data" => $shippings
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
            "address" => "required|string",
            "number" => "required|string",
            "rt" => "required|string",
            "rw" => "required|string",
            "village" => "required|string",
            "district" => "required|string",
            "city" => "required|string",
            "province" => "required|string",
            "postalCode" => "string",
        ]);

        try {
            $data = [
                "uuid" => Uuid::uuid(),
                "user_uuid" => $request->get("user_uuid"),
                "address" => $request->get("address"),
                "number" => $request->get("number"),
                "rt" => $request->get("rt"),
                "rw" => $request->get("rw"),
                "village" => $request->get("village"),
                "district" => $request->get("district"),
                "city" => $request->get("city"),
                "province" => $request->get("province"),
                "postalCode" => $request->get("postalCode"),
            ];

            $shipping = Shipping::create($data);

            return response()->json([
                "success" => true,
                "message" => "Create shipping success",
                "data" => [
                    "shipping" => [
                        "uuid" => $shipping->uuid
                    ]
                ]
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
        $shipping = Shipping::where("uuid", $uuid)->first();

        if (!$shipping) {
            return response()->json([
                "success" => true,
                "message" => "Show detail shipping failed",
                "data" => [
                    "shipping" => "Shipping not found",
                ]
            ], 400);
        }

        try {
            return response()->json([
                "success" => true,
                "message" => "Show detail shipping success",
                "data" => [
                    "shipping" => $shipping,
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

        $shipping = Shipping::where("uuid", $uuid)->first();

        if (!$shipping) {
            return response()->json([
                "success" => true,
                "message" => "Edit shipping failed",
                "data" => [
                    "shipping" => "Shipping not found",
                ]
            ], 400);
        }

        $request->validate([
            "address" => "required|string",
            "number" => "string",
            "rt" => "required|string",
            "rw" => "required|string",
            "village" => "required|string",
            "district" => "required|string",
            "city" => "required|string",
            "province" => "required|string",
            "postalCode" => "string",
            "active" => "required|string",
        ]);

        try {
            $data = [
                "address" => $request->get("address"),
                "number" => $request->get("number"),
                "rt" => $request->get("rt"),
                "rw" => $request->get("rw"),
                "village" => $request->get("village"),
                "district" => $request->get("district"),
                "city" => $request->get("city"),
                "province" => $request->get("province"),
                "postalCode" => $request->get("postalCode"),
                "active" => $request->get("active"),
            ];

            $shipping->update($data);

            return response()->json([
                "success" => true,
                "message" => "Edit shipping success",
                "data" => [
                    "shipping" => [
                        "uuid" => $shipping->uuid
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
        $shipping = Shipping::where("uuid", $uuid)->first();

        if (!$shipping) {
            return response()->json([
                "success" => true,
                "message" => "Delete shipping failed",
                "data" => [
                    "shipping" => "Shipping not found",
                ]
            ], 400);
        }

        try {
            $shipping->delete();

            return response()->json([
                "success" => true,
                "message" => "Delete shipping success",
                "data" => [
                    "shipping" => [
                        "uuid" => $shipping->uuid
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

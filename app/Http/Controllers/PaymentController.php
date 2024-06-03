<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $payments = Payment::orderBy("created_at", "desc")->get()->toArray();

            $payments = Arr::map($payments, function ($value) {
                return [
                    "payment" => $value
                ];
            });

            return response()->json([
                "success" => true,
                "message" => "Show list payments success",
                "data" => $payments
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
    public function store($request)
    {
        try {
            $data = [
                "uuid" => Uuid::uuid(),
                "parent_uuid" => $request["parent_uuid"],
                "shipping_uuid" => $request["shipping_uuid"],
                "method" => $request["method"],
                "provider" => $request["provider"],
                "total" => $request["total"],
            ];

            $payment = Payment::create($data);

            return response()->json([
                "success" => true,
                "message" => "Create payment success",
                "data" => [
                    "payment" => [
                        "uuid" => $payment->uuid
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
        $payment = Payment::where("uuid", $uuid)->first();

        if (!$payment) {
            return response()->json([
                "success" => true,
                "message" => "Show detail payment failed",
                "data" => [
                    "payment" => "Payment not found",
                ]
            ], 400);
        }

        try {
            return response()->json([
                "success" => true,
                "message" => "Show detail payment success",
                "data" => [
                    "payment" => $payment,
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
        $payment = Payment::where("uuid", $uuid)->first();

        if (!$payment) {
            return response()->json([
                "success" => true,
                "message" => "Edit payment failed",
                "data" => [
                    "payment" => "Payment not found",
                ]
            ], 400);
        }

        $request->validate([
            "paid" => "required|string",
        ]);

        try {
            $data = [
                "paid" => $request->get("paid"),
            ];

            $payment = $payment->update($data);

            return response()->json([
                "success" => true,
                "message" => "Edit payment success",
                "data" => [
                    "payment" => [
                        "uuid" => $payment->uuid
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
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }
}

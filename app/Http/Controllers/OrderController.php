<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $wrapper = collect([]);

            $orders = Order::orderBy('created_at', 'desc');

            if ($request->has('user_uuid') && $request->input('user_uuid')) {
                $user_uuid = $request->input('user_uuid');
                $orders = $orders->where("user_uuid",  $user_uuid);
            }

            if ($request->has('status') && $request->input('status')) {
                $status = $request->input('status');
                $orders = $orders->where("status",  $status);
            }

            $orders = $orders->get()->toArray();

            foreach ($orders as $key => $order) {
                $existParent = $wrapper->contains(function ($value, $key) use ($order) {
                    return $value["parent_uuid"] === $order["parent_uuid"];
                });

                if (!$existParent) {
                    $wrapper->push(["parent_uuid" => $order["parent_uuid"], "user_uuid" => $order["user_uuid"], "detail" => []]);
                }

                $wrapper = $wrapper->map(function ($item, $i) use ($order) {
                    if ($item["parent_uuid"] === $order["parent_uuid"]) {
                        array_push($item["detail"], $order);
                    }

                    return $item;
                });
            }

            $wrapper = $wrapper->map(function ($item, $i) {
                $payment = Payment::select("paid", "total", "payment_date")->where("parent_uuid", $item["parent_uuid"])->first();

                $item["payment"] = $payment;

                $user = User::select("name")->where("uuid", $item["user_uuid"])->first();

                $item["user"] = $user;

                $item["detail"] = collect($item["detail"])->map(function ($dtl, $idtl) {
                    $product = Product::where("uuid", $dtl["product_uuid"])->first();

                    $dtl["product"] = $product;

                    return $dtl;
                });

                return $item;
            });

            return response()->json([
                "success" => true,
                "message" => "Show list orders success",
                "data" => $wrapper
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $parent_uuid = Uuid::uuid();
        $orders = $request->get("orders");
        $user_uuid = $request->get("user")["uuid"];
        $shipping_uuid = $request->get("shipping")["uuid"];
        $payment = $request->get("payment");

        try {
            foreach ($orders as $key => $value) {
                $data = [
                    "uuid" => Uuid::uuid(),
                    "user_uuid" => $user_uuid,
                    "parent_uuid" => $parent_uuid,
                    "product_uuid" => $value["detail"]["uuid"],
                    "note" => $value["detail"]["note"],
                    "total" =>  $value["detail"]["total"],
                    "price" =>  $value["detail"]["price"],
                    "order_date" => Carbon::now()
                ];

                Order::create($data);
            }

            $paymentData = [
                "uuid" => Uuid::uuid(),
                "parent_uuid" => $parent_uuid,
                "shipping_uuid" => $shipping_uuid,
                "method" => $payment["method"],
                "provider" => $payment["provider"],
                "total" => $payment["priceTotal"],
            ];

            Payment::create($paymentData);

            Shipping::where("uuid", $shipping_uuid)->where("user_uuid", $user_uuid)->update(["status" => "Y"]);

            Shipping::where("uuid", "!=", $shipping_uuid)->where("user_uuid", $user_uuid)->update(["status" => "N"]);

            return response()->json([
                "success" => true,
                "message" => "Order success",
                "data" => [
                    "order" => [
                        "parent_uuid" => $parent_uuid
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
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        try {
            $wrapper = collect([]);

            $orders = Order::where('parent_uuid', $uuid)->orderBy('created_at', 'desc')->get()->toArray();

            foreach ($orders as $key => $order) {
                $existParent = $wrapper->contains(function ($value, $key) use ($order) {
                    return $value["parent_uuid"] === $order["parent_uuid"];
                });

                if (!$existParent) {
                    $wrapper->push(["parent_uuid" => $order["parent_uuid"], "user_uuid" => $order["user_uuid"], "detail" => []]);
                }

                $wrapper = $wrapper->map(function ($item, $i) use ($order) {
                    if ($item["parent_uuid"] === $order["parent_uuid"]) {
                        array_push($item["detail"], $order);
                    }

                    return $item;
                });
            }

            $wrapper = $wrapper->map(function ($item, $i) {
                $payment = Payment::select("paid", "total", "payment_date")->where("parent_uuid", $item["parent_uuid"])->first();

                $item["payment"] = $payment;

                $user = User::select("name")->where("uuid", $item["user_uuid"])->first();

                $item["user"] = $user;

                $item["detail"] = collect($item["detail"])->map(function ($dtl, $idtl) {
                    $product = Product::where("uuid", $dtl["product_uuid"])->first();

                    $dtl["product"] = $product;

                    return $dtl;
                });

                return $item;
            });

            return response()->json([
                "success" => true,
                "message" => "Show detail order success",
                "data" => $wrapper->first()
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
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }

    /**
     * Cancel the specified resource from storage.
     */
    public function cancel(string $uuid)
    {

        $order = Order::where("parent_uuid", $uuid);

        if (!$order) {
            return response()->json([
                "success" => true,
                "message" => "Update order failed",
                "data" => [
                    "order" => "Order data not found",
                ]
            ], 400);
        }

        try {
            $updData = ["status" => "C"];

            $order->update($updData);

            return response()->json([
                "success" => true,
                "message" => "Cancel order success",
                "data" => [
                    "order" => [
                        "parent_uuid" => $uuid
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
     * Confirm the specified resource from storage.
     */
    public function confirm(string $uuid)
    {
        $order = Order::where("parent_uuid", $uuid);

        if (!$order) {
            return response()->json([
                "success" => true,
                "message" => "Update order failed",
                "data" => [
                    "order" => "Order data not found",
                ]
            ], 400);
        }

        try {
            $updData = ["status" => "P"];

            $order->update($updData);

            $payment = Payment::where("parent_uuid", $uuid)->first();

            $updPayment = ["paid" => "Y", "payment_date" => Carbon::now()];

            $payment->update($updPayment);

            foreach (Order::where("parent_uuid", $uuid)->get()->toArray() as $key => $value) {
                $product_uuid = $value["product_uuid"];

                $product = Product::where("uuid", $product_uuid)->first();

                $sold = $product["sold"] ? $product["sold"] + $value["total"] : $value["total"];

                $stock = $product["stock"] - $value["total"];

                $updDataProd = ["sold" => $sold, "stock" => $stock];

                $product->update($updDataProd);
            }

            return response()->json([
                "success" => true,
                "message" => "Confirmation payment order success",
                "data" => [
                    "order" => [
                        "parent_uuid" => $uuid
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
     * Delivery the specified resource from storage.
     */
    public function delivery(string $uuid)
    {
        $order = Order::where("parent_uuid", $uuid);

        if (!$order) {
            return response()->json([
                "success" => true,
                "message" => "Update order failed",
                "data" => [
                    "order" => "Order data not found",
                ]
            ], 400);
        }

        try {
            $updData = ["status" => "D"];

            $order->update($updData);

            return response()->json([
                "success" => true,
                "message" => "Delivery order success",
                "data" => [
                    "order" => [
                        "parent_uuid" => $uuid
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
     * Received the specified resource from storage.
     */
    public function received(string $uuid)
    {
        $order = Order::where("parent_uuid", $uuid);

        if (!$order) {
            return response()->json([
                "success" => true,
                "message" => "Update order failed",
                "data" => [
                    "order" => "Order data not found",
                ]
            ], 400);
        }

        try {
            $updData = ["status" => "Y"];

            $order->update($updData);

            return response()->json([
                "success" => true,
                "message" => "Received order success",
                "data" => [
                    "order" => [
                        "parent_uuid" => $uuid
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

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::orderBy("created_at", "desc")->get()->toArray();

            $products = Arr::map($products, function ($value) {
                return [
                    "product" => $value
                ];
            });

            return response()->json([
                "success" => true,
                "message" => "Show list product success",
                "data" => $products
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
            "description" => "required|string",
            "stock" => "required|numeric",
            "price" => "required|numeric",
            "image" => [
                "required",
                "image",
                "file",
                "mimes:jpg,png,jpeg",
                "max:5000",
            ]
        ]);

        $path = "uploads/product/";

        $filename = Carbon::now()->toDateString() . "_" . strtotime(Carbon::now()->toTimeString()) .  "." .  $request->file("image")->getClientOriginalExtension();

        try {
            $data = collect([
                "uuid" => Uuid::uuid(),
                "name" => $request->get("name"),
                "description" => $request->get("description"),
                "stock" => $request->get("stock"),
                "price" => $request->get("price"),
            ]);

            if ($request->file("image")->storeAs($path, $filename)) {
                $data = $data->merge([
                    "image" => url("/") . "/storage/uploads/product/" . $filename,
                    "filename" => $filename
                ]);
            }

            $insProduct = Product::create($data->all());

            return response()->json([
                "success" => true,
                "message" => "Create product success",
                "data" => [
                    "product" => [
                        "uuid" => $insProduct->uuid
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
        $product = Product::where("uuid", $uuid)->first();

        if (!$product) {
            return response()->json([
                "success" => true,
                "message" => "Show detail product failed",
                "data" => [
                    "product" => "Product not found",
                ]
            ], 400);
        }

        try {
            return response()->json([
                "success" => true,
                "message" => "Show detail product success",
                "data" => [
                    "product" => $product,
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
        $product = Product::where("uuid", $uuid)->first();

        if (!$product) {
            return response()->json([
                "success" => true,
                "message" => "Show detail product failed",
                "data" => [
                    "product" => "Product not found",
                ]
            ], 400);
        }

        $validateData = [
            "name" => "required|string",
            "description" => "required|string",
            "stock" => "required|numeric",
            "price" => "required|numeric",
            "image" => []
        ];

        if ($request->file("image")) {
            $validateData["image"] = [
                "required",
                "image",
                "file",
                "mimes:jpg,png,jpeg",
                "max:5000",
            ];
        }

        $request->validate($validateData);

        $path = "uploads/product/";

        $filename = Carbon::now()->toDateString() . "_" . strtotime(Carbon::now()->toTimeString()) .  "." .  $request->file("image")->getClientOriginalExtension();

        try {
            $data = collect([
                "name" => $request->get("name"),
                "description" => $request->get("description"),
                "stock" => $request->get("stock"),
                "price" => $request->get("price"),
            ]);



            if ($request->file("image") && $request->file("image") !== "") {
                if (file_exists("storage/uploads/product/" . $product->filename)) {
                    unlink("storage/uploads/product/" . $product->filename);
                }

                if ($request->file("image")->storeAs($path, $filename)) {
                    $data = $data->merge([
                        "image" => url("/") . "/storage/uploads/product/" . $filename,
                        "filename" => $filename
                    ]);
                }
            }

            $product->update($data->all());

            return response()->json([
                "success" => true,
                "message" => "Edit product success",
                "data" => [
                    "product" => [
                        "uuid" => $product->uuid
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
    public function destroy(string $uuid)
    {
        $product = Product::where("uuid", $uuid)->first();

        if (!$product) {
            return response()->json([
                "success" => true,
                "message" => "Delete product failed",
                "data" => [
                    "product" => "Product not found",
                ]
            ], 400);
        }

        try {
            $product->delete();

            return response()->json([
                "success" => true,
                "message" => "Delete product success",
                "data" => [
                    "product" => [
                        "uuid" => $product->uuid
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
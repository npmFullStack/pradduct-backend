<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
  public function index(Request $request)
  {
    $products = Product::with("endUser")
      ->where("status", 1)
      ->get()
      ->map(function ($product) {
        return [
          "id" => $product->id,
          "name" => $product->name,
          "price" => $product->price,
          "description" => $product->description,
          "image" => $product->image
            ? asset("storage/{$product->image}")
            : null,
          "end_user" => [
            "firstname" => $product->endUser->firstname,
            "lastname" => $product->endUser->lastname,
          ],
        ];
      });

    return response()->json($products);
  }

  public function userProducts(Request $request)
  {
    $products = Product::where("end_users_id", $request->user()->id)
      ->where("status", 1)
      ->get()
      ->map(function ($product) {
        return [
          "id" => $product->id,
          "name" => $product->name,
          "price" => $product->price,
          "description" => $product->description,
          "image" => $product->image ? Storage::url($product->image) : null,
          "end_user" => [
            "firstname" => $product->endUser->firstname,
            "lastname" => $product->endUser->lastname,
          ],
        ];
      });

    return response()->json($products);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      "name" => "required|string|max:255",
      "description" => "required|string",
      "price" => "required|numeric|min:0",
      "image" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
    ]);

    $imagePath = null;
    if ($request->hasFile("image")) {
      // Generate a unique filename
      $filename =
        time() . "_" . $request->file("image")->getClientOriginalName();
      $imagePath = $request
        ->file("image")
        ->storeAs("products", $filename, "public");
    }

    $product = Product::create([
      "name" => $validated["name"],
      "description" => $validated["description"],
      "price" => $validated["price"],
      "image" => $imagePath,
      "end_users_id" => $request->user()->id,
      "status" => 1,
    ]);

    return response()->json(
      [
        "product" => $product,
        "image_url" => $imagePath ? asset("storage/$imagePath") : null,
        "message" => "Product created successfully",
      ],
      201
    );
  }

  public function show(Product $product)
{
    // Check if product belongs to authenticated user
    if (request()->user()->id !== $product->end_users_id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    return response()->json([
        'product' => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'description' => $product->description,
            'image' => $product->image ? asset("storage/{$product->image}") : null,
            'currentImage' => $product->image ? asset("storage/{$product->image}") : null
        ]
    ]);
}

  public function update(Request $request, Product $product)
  {
    if ($product->end_users_id !== $request->user()->id) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $validated = $request->validate([
      "name" => "required|string|max:255",
      "description" => "required|string",
      "price" => "required|numeric|min:0",
      "image" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
    ]);

    $imagePath = $product->image;
    if ($request->hasFile("image")) {
      // Delete old image if exists
      if ($imagePath) {
        Storage::disk("public")->delete($imagePath);
      }
      // Generate new filename
      $filename =
        time() . "_" . $request->file("image")->getClientOriginalName();
      $imagePath = $request
        ->file("image")
        ->storeAs("products", $filename, "public");
    }

    $product->update([
      "name" => $validated["name"],
      "description" => $validated["description"],
      "price" => $validated["price"],
      "image" => $imagePath,
    ]);

    return response()->json([
      "product" => $product,
      "image_url" => $imagePath ? asset("storage/$imagePath") : null,
      "message" => "Product updated successfully",
    ]);
  }

  public function destroy(Product $product)
  {
    // Soft delete by setting status to 0
    $product->update(["status" => 0]);

    return response()->json(["message" => "Product deleted successfully"]);
  }
}

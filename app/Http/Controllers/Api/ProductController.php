<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
public function index(Request $request)
{
    $products = Product::with('endUser')
        ->where('status', 1)
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'description' => $product->description,
                'image' => $product->image,
                'end_user' => [
                    'firstname' => $product->endUser->firstname,
                    'lastname' => $product->endUser->lastname
                ]
            ];
        });

    return response()->json($products);
}

    public function userProducts(Request $request)
    {
        $products = Product::where('end_users_id', $request->user()->id)
            ->where('status', 1)
            ->get();

        return response()->json($products);
    }

    public function destroy(Product $product)
    {
        // Soft delete by setting status to 0
        $product->update(['status' => 0]);
        
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
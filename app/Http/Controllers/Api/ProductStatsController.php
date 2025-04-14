<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductStatsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $totalProducts = Product::count();
        $userProducts = Product::where('end_users_id', $user->id)->count();

        return response()->json([
            'totalProducts' => $totalProducts,
            'userProducts' => $userProducts
        ]);
    }
}
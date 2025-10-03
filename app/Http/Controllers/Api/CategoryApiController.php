<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryApiController extends Controller
{
    public function index()
    {
        $categories = Category::with('stores')
        ->where('status', 'active')
        ->where('show', true)
        ->get();

        return response()->json([
            'categories' => $categories,
        ], 200);
        // return response()->json(Category::all());
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $category
        ]);
    }

}

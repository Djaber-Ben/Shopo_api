<?php

namespace App\Http\Controllers\Api;

use App\Models\Slider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SliderApiController extends Controller
{
    public function index()
    {
        return response()->json(Slider::all());
    }

    public function show($id)
    {
        $slider = Slider::find($id);

        if (!$slider) {
            return response()->json([
                'status' => false,
                'message' => 'slider not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $slider
        ]);
    }
}

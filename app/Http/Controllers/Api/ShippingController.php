<?php

namespace App\Http\Controllers\Api;

use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ShippingController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $shippings = Shipping::all();
        return response()->json([
            'success' => true,
            'data'    => $shippings
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shipping_method'    => 'required|string|max:100',
            'base_shipping_cost' => 'required|numeric|min:0',
            'estimated_time'     => 'required|string|max:50', 
            'is_active'          => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $shipping = Shipping::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Shipping method added successfully',
                'data'    => $shipping
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $shipping = Shipping::find($id);
        if (!$shipping) {
            return response()->json([
                'success' => false, 
                'message' => 'Shipping method not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'shipping_method'    => 'sometimes|required|string|max:100',
            'base_shipping_cost' => 'sometimes|required|numeric|min:0',
            'estimated_time'     => 'sometimes|required|string|max:50',
            'is_active'          => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shipping->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Shipping method updated successfully',
                'data'    => $shipping
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $shipping = Shipping::find($id);
        if (!$shipping) {
            return response()->json([
                'success' => false, 
                'message' => 'Shipping method not found'
                ], 404);
        }

        $shipping->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping method deleted successfully'
        ], 200);
    }
}

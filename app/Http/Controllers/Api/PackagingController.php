<?php

namespace App\Http\Controllers\Api;

use App\Models\Packaging;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PackagingController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $packagings = Packaging::all();
        return response()->json([
            'success' => true,
            'data'    => $packagings
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'packaging_name' => 'required|string|max:100',
            'base_packaging_cost' => 'required|numeric|min:0',
            'packaging_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'packaging_description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            if ($request->hasFile('packaging_image')) {
                $data['packaging_image'] = $request->file('packaging_image')->store('packaging', 'public');
            }

            $packaging = Packaging::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Packaging added successfully',
                'data'    => $packaging
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
        $packaging = Packaging::find($id);
        if (!$packaging) {
            return response()->json([
                'success' => false, 
                'message' => 'Packaging not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'packaging_method' => 'sometimes|required|string|max:100',
            'base_packaging_cost' => 'sometimes|required|numeric|min:0',
            'packaging_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'packaging_description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            if ($request->hasFile('packaging_image')) {
                if ($packaging->packaging_image && Storage::disk('public')->exists($packaging->packaging_image)) {
                    Storage::disk('public')->delete($packaging->packaging_image);
                }
                $data['packaging_image'] = $request->file('packaging_image')->store('packaging', 'public');
            }

            $packaging->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Packaging updated successfully',
                'data'    => $packaging
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
        $packaging = Packaging::find($id);
        if (!$packaging) {
            return response()->json([
                'success' => false, 
                'message' => 'Packaging not found'
            ], 404);
        }

        if ($packaging->packaging_image && Storage::disk('public')->exists($packaging->packaging_image)) {
            Storage::disk('public')->delete($packaging->packaging_image);
        }

        $packaging->delete();

        return response()->json([
            'success' => true,
            'message' => 'Packaging deleted successfully'
        ], 200);
    }
}

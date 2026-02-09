<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Category::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:100|unique:categories,category_name',
            'slug' => 'required|string|max:100|unique:categories,slug'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['category_name']);
        }

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category Created Succesfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $category = Category::with('products:id,category_id,product_name')->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category Not Found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category Not Found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'category_name')->ignore($category->id)
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'slug')->ignore ($category->id),
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category Not Found'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category Deleted Succesfully'
        ], 200);
    }

    public function restore(string $id): JsonResponse
    {
        $category = Category::onlyTrashed()->find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category Not Found in Trash'
            ], 404);
        }

        $category->restore();

        return response()->json([
            'success' => true,
            'message' => 'Category Restored Successfully',
            'data' => $category
        ], 200);
    }
}

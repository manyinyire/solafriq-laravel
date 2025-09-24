<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SolarSystem;
use App\Http\Resources\SolarSystemResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SolarSystemController extends Controller
{
    /**
     * Display a listing of solar systems.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SolarSystem::with(['features', 'products', 'specifications'])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc');

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Filter by popular
        if ($request->boolean('popular')) {
            $query->popular();
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('capacity', 'like', "%{$search}%");
            });
        }

        // Price filtering
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        $systems = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => SolarSystemResource::collection($systems->items()),
            'meta' => [
                'current_page' => $systems->currentPage(),
                'last_page' => $systems->lastPage(),
                'per_page' => $systems->perPage(),
                'total' => $systems->total(),
            ],
        ]);
    }

    /**
     * Display the specified solar system.
     */
    public function show(SolarSystem $solarSystem): JsonResponse
    {
        $solarSystem->load(['features', 'products', 'specifications']);

        return response()->json([
            'data' => new SolarSystemResource($solarSystem),
        ]);
    }

    /**
     * Store a newly created solar system (Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', SolarSystem::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string|max:500',
            'capacity' => 'required|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'installment_price' => 'nullable|numeric|min:0',
            'installment_months' => 'nullable|integer|min:1',
            'image_url' => 'nullable|url',
            'gallery_images' => 'nullable|array',
            'use_case' => 'nullable|string',
            'gradient_colors' => 'nullable|string',
            'is_popular' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $solarSystem = SolarSystem::create($validated);
        $solarSystem->load(['features', 'products', 'specifications']);

        return response()->json([
            'data' => new SolarSystemResource($solarSystem),
            'message' => 'Solar system created successfully',
        ], 201);
    }

    /**
     * Update the specified solar system (Admin only).
     */
    public function update(Request $request, SolarSystem $solarSystem): JsonResponse
    {
        $this->authorize('update', $solarSystem);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'short_description' => 'sometimes|string|max:500',
            'capacity' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'installment_price' => 'nullable|numeric|min:0',
            'installment_months' => 'nullable|integer|min:1',
            'image_url' => 'nullable|url',
            'gallery_images' => 'nullable|array',
            'use_case' => 'nullable|string',
            'gradient_colors' => 'nullable|string',
            'is_popular' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $solarSystem->update($validated);
        $solarSystem->load(['features', 'products', 'specifications']);

        return response()->json([
            'data' => new SolarSystemResource($solarSystem),
            'message' => 'Solar system updated successfully',
        ]);
    }

    /**
     * Remove the specified solar system (Admin only).
     */
    public function destroy(SolarSystem $solarSystem): JsonResponse
    {
        $this->authorize('delete', $solarSystem);

        $solarSystem->delete();

        return response()->json([
            'message' => 'Solar system deleted successfully',
        ]);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolarSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SolarSystemController extends Controller
{
    public function index()
    {
        $systems = SolarSystem::with(['features', 'products', 'specifications'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/SolarSystems', [
            'systems' => $systems,
        ]);
    }

    public function show($id)
    {
        $system = SolarSystem::with(['features', 'products', 'specifications'])
            ->findOrFail($id);

        return response()->json($system);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string|max:500',
            'capacity' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'installment_price' => 'nullable|numeric|min:0',
            'installment_months' => 'nullable|integer|min:1',
            'image_url' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'use_case' => 'nullable|string|max:255',
            'gradient_colors' => 'nullable|string',
            'is_popular' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $system = SolarSystem::create($validated);

        return response()->json([
            'message' => 'Solar system created successfully',
            'system' => $system,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $system = SolarSystem::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string|max:500',
            'capacity' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'installment_price' => 'nullable|numeric|min:0',
            'installment_months' => 'nullable|integer|min:1',
            'image_url' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'use_case' => 'nullable|string|max:255',
            'gradient_colors' => 'nullable|string',
            'is_popular' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $system->update($validated);

        return response()->json([
            'message' => 'Solar system updated successfully',
            'system' => $system->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $system = SolarSystem::findOrFail($id);
        $system->delete();

        return response()->json([
            'message' => 'Solar system deleted successfully',
        ]);
    }
}
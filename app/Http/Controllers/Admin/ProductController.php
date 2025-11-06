<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Filter by category if provided
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%')
                  ->orWhere('model', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Products', [
            'products' => $products,
            'filters' => [
                'category' => $request->category ?? 'all',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:SOLAR_PANEL,INVERTER,BATTERY,CHARGE_CONTROLLER,MOUNTING,CABLES,ACCESSORIES',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string',
            'specifications' => 'nullable|array',
            'stock_quantity' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'power_rating' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:SOLAR_PANEL,INVERTER,BATTERY,CHARGE_CONTROLLER,MOUNTING,CABLES,ACCESSORIES',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string',
            'specifications' => 'nullable|array',
            'stock_quantity' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'power_rating' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Get products by category for the builder
     */
    public function byCategory($category)
    {
        $products = Product::active()
            ->category($category)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    /**
     * Export products to CSV
     */
    public function exportCsv(Request $request)
    {
        $query = Product::query();

        // Apply same filters as index
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%')
                  ->orWhere('model', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->orderBy('sort_order')->orderBy('name')->get();

        $filename = 'products_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Brand',
                'Model',
                'Category',
                'Price',
                'Stock Quantity',
                'Unit',
                'Power Rating (W)',
                'Capacity (Ah)',
                'Is Active',
                'Sort Order',
                'Created At'
            ]);

            // Add data rows
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->brand,
                    $product->model,
                    $product->category,
                    $product->price,
                    $product->stock_quantity,
                    $product->unit,
                    $product->power_rating,
                    $product->capacity,
                    $product->is_active ? 'Yes' : 'No',
                    $product->sort_order,
                    $product->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SolarSystemBuilderService;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CustomBuilderController extends Controller
{
    protected $builderService;

    public function __construct(SolarSystemBuilderService $builderService)
    {
        $this->builderService = $builderService;
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_consumption' => 'required|numeric|min:1',
            'location' => 'required|string',
            'property_type' => 'required|in:RESIDENTIAL,COMMERCIAL,INDUSTRIAL',
            'budget_range' => 'nullable|array',
            'budget_range.min' => 'nullable|numeric|min:0',
            'budget_range.max' => 'nullable|numeric|min:0',
            'backup_hours' => 'required|integer|min:1|max:24',
            'appliances' => 'nullable|array',
            'appliances.*.name' => 'string',
            'appliances.*.power_rating' => 'numeric|min:1',
            'appliances.*.usage_hours' => 'numeric|min:0.1|max:24',
            'preferences' => 'nullable|array',
            'preferences.panel_type' => 'nullable|in:MONOCRYSTALLINE,POLYCRYSTALLINE,THIN_FILM',
            'preferences.inverter_type' => 'nullable|in:STRING,POWER_OPTIMIZER,MICROINVERTER',
            'preferences.battery_type' => 'nullable|in:LITHIUM_ION,LEAD_ACID,GEL',
        ]);

        try {
            $calculation = $this->builderService->calculateSystemRequirements($validated);

            // Cache the calculation for the session (using SHA256 for cache key)
            $cacheKey = 'calculation_' . hash('sha256', json_encode($validated) . (Auth::id() ?? 'guest'));
            Cache::put($cacheKey, $calculation, now()->addHours(2));

            return response()->json([
                'calculation' => $calculation,
                'cache_key' => $cacheKey,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'System calculation failed',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function validateSystem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'system_components' => 'required|array',
            'system_components.solar_panels' => 'required|array',
            'system_components.solar_panels.*.id' => 'required|exists:solar_systems,id',
            'system_components.solar_panels.*.quantity' => 'required|integer|min:1',
            'system_components.inverters' => 'required|array',
            'system_components.inverters.*.id' => 'required|exists:solar_systems,id',
            'system_components.inverters.*.quantity' => 'required|integer|min:1',
            'system_components.batteries' => 'nullable|array',
            'system_components.batteries.*.id' => 'required|exists:solar_systems,id',
            'system_components.batteries.*.quantity' => 'required|integer|min:1',
            'installation_requirements' => 'nullable|array',
            'installation_requirements.roof_type' => 'nullable|string',
            'installation_requirements.roof_condition' => 'nullable|string',
            'installation_requirements.shading_issues' => 'nullable|boolean',
        ]);

        try {
            $validation = $this->builderService->validateSystemConfiguration($validated);

            return response()->json($validation);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'System validation failed',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function save(Request $request): JsonResponse
    {
        $this->middleware('auth:sanctum');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cache_key' => 'required|string',
            'system_components' => 'required|array',
            'estimated_cost' => 'required|numeric|min:0',
            'estimated_savings' => 'nullable|numeric|min:0',
            'payback_period' => 'nullable|numeric|min:0',
        ]);

        // Retrieve the calculation from cache
        $calculation = Cache::get($validated['cache_key']);
        if (!$calculation) {
            return response()->json([
                'message' => 'Calculation data not found or expired'
            ], 404);
        }

        // Save the custom system configuration
        $savedSystem = Auth::user()->customSystems()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'system_components' => $validated['system_components'],
            'calculation_data' => $calculation,
            'estimated_cost' => $validated['estimated_cost'],
            'estimated_savings' => $validated['estimated_savings'],
            'payback_period' => $validated['payback_period'],
        ]);

        return response()->json($savedSystem, 201);
    }

    public function saved(): JsonResponse
    {
        $this->middleware('auth:sanctum');

        $savedSystems = Auth::user()->customSystems()
            ->latest()
            ->paginate(10);

        return response()->json($savedSystems);
    }

    public function getCalculation(string $cacheKey): JsonResponse
    {
        $calculation = Cache::get($cacheKey);

        if (!$calculation) {
            return response()->json([
                'message' => 'Calculation not found or expired'
            ], 404);
        }

        return response()->json($calculation);
    }

    public function generateQuote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cache_key' => 'required|string',
            'customer_info' => 'required|array',
            'customer_info.name' => 'required|string',
            'customer_info.email' => 'required|email',
            'customer_info.phone' => 'required|string',
            'customer_info.address' => 'required|string',
            'installation_preferences' => 'nullable|array',
            'payment_option' => 'required|in:FULL,INSTALLMENT',
            'installment_months' => 'required_if:payment_option,INSTALLMENT|nullable|integer|min:3|max:60',
        ]);

        $calculation = Cache::get($validated['cache_key']);
        if (!$calculation) {
            return response()->json([
                'message' => 'Calculation data not found or expired'
            ], 404);
        }

        try {
            $quote = $this->builderService->generateQuote($calculation, $validated);

            return response()->json($quote);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Quote generation failed',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get products by category for the custom builder
     * Only returns products that are in stock
     */
    public function getProducts(Request $request): JsonResponse
    {
        $category = $request->input('category');

        // Only get active products with stock > 0
        $query = Product::active()
            ->where('stock_quantity', '>', 0)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($category) {
            $query->where('category', $category);
        }

        $products = $query->get();

        // Group products by category
        $groupedProducts = [
            'SOLAR_PANEL' => $products->where('category', 'SOLAR_PANEL')->values(),
            'INVERTER' => $products->where('category', 'INVERTER')->values(),
            'BATTERY' => $products->where('category', 'BATTERY')->values(),
            'CHARGE_CONTROLLER' => $products->where('category', 'CHARGE_CONTROLLER')->values(),
            'MOUNTING' => $products->where('category', 'MOUNTING')->values(),
            'CABLES' => $products->where('category', 'CABLES')->values(),
            'ACCESSORIES' => $products->where('category', 'ACCESSORIES')->values(),
        ];

        return response()->json($groupedProducts);
    }

    /**
     * Add custom system to cart
     */
    public function addToCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'system_name' => 'required|string|max:255',
            'components' => 'required|array',
            'components.*.product_id' => 'required|exists:products,id',
            'components.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // Get or create cart
                $cart = $this->getCart();

                // Validate stock availability for all components
                foreach ($validated['components'] as $component) {
                    $product = Product::find($component['product_id']);
                    $quantity = $component['quantity'];

                    if ($quantity > $product->stock_quantity) {
                        return response()->json([
                            'message' => "Insufficient stock for {$product->name}. Only {$product->stock_quantity} units available.",
                            'error' => 'INSUFFICIENT_STOCK',
                            'product' => $product->name,
                            'requested' => $quantity,
                            'available' => $product->stock_quantity,
                        ], 400);
                    }
                }

                // Add each component as a separate cart item
                $addedItems = [];
                foreach ($validated['components'] as $component) {
                    $product = Product::find($component['product_id']);
                    $quantity = $component['quantity'];

                    // Check if item already exists in cart
                    $existingItem = $cart->items()
                        ->where('product_id', $product->id)
                        ->where('item_type', 'custom_component')
                        ->first();

                    if ($existingItem) {
                        $newQuantity = $existingItem->quantity + $quantity;
                        if ($newQuantity > $product->stock_quantity) {
                            return response()->json([
                                'message' => "Cannot add more {$product->name}. Stock limit reached.",
                                'error' => 'INSUFFICIENT_STOCK',
                            ], 400);
                        }
                        $existingItem->update(['quantity' => $newQuantity]);
                        $addedItems[] = $existingItem;
                    } else {
                        $cartItem = $cart->items()->create([
                            'product_id' => $product->id,
                            'item_type' => 'custom_component',
                            'quantity' => $quantity,
                            'price' => $product->price,
                            'custom_system_name' => $validated['system_name'],
                        ]);
                        $addedItems[] = $cartItem;
                    }
                }

                return response()->json([
                    'message' => 'Custom system added to cart successfully',
                    'items_added' => count($addedItems),
                    'cart_count' => $cart->items()->count(),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get or create cart for current user/session
     */
    private function getCart(): Cart
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        if ($userId) {
            return Cart::firstOrCreate([
                'user_id' => $userId,
                'session_id' => null,
            ]);
        }

        return Cart::firstOrCreate([
            'user_id' => null,
            'session_id' => $sessionId,
        ]);
    }
}
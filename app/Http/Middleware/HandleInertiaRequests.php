<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\CompanySetting;
use App\Models\Cart;
use App\Models\SolarSystem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        // Get cart data
        $cartData = $this->getCartData($request);

        // Get active solar systems for menu
        $solarSystems = SolarSystem::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'short_description', 'capacity'])
            ->map(function ($system) {
                return [
                    'id' => $system->id,
                    'title' => $system->name,
                    'href' => "/systems/{$system->id}",
                    'description' => $system->short_description ?? "Solar system with {$system->capacity}kW capacity",
                ];
            });

        // Get unique product categories for menu
        $productCategories = Product::active()
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->map(function ($category) {
                return [
                    'title' => ucfirst($category),
                    'href' => "/products/category/{$category}",
                    'description' => ucfirst($category) . " products",
                ];
            });

        return array_merge(parent::share($request), [
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'is_admin' => $request->user()->isAdmin(),
                    'is_client' => $request->user()->isClient(),
                ] : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
            'cart' => $cartData,
            'companySettings' => CompanySetting::getPublic(),
            'solarSystems' => $solarSystems,
            'productCategories' => $productCategories,
            'features' => [
                'warranty_claims' => env('ENABLE_WARRANTY_CLAIMS', true),
                'custom_builder' => env('ENABLE_CUSTOM_BUILDER', true),
            ],
        ]);
    }

    private function getCartData(Request $request): array
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $cart = null;
        if ($userId) {
            $cart = Cart::where('user_id', $userId)
                ->where('session_id', null)
                ->first();
        } else {
            $cart = Cart::where('user_id', null)
                ->where('session_id', $sessionId)
                ->first();
        }

        return [
            'item_count' => $cart ? $cart->item_count : 0,
            'total' => $cart ? $cart->total : 0,
        ];
    }
}
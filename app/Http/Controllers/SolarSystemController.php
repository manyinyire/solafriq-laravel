<?php

namespace App\Http\Controllers;

use App\Models\SolarSystem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SolarSystemController extends Controller
{
    public function index()
    {
        $systems = SolarSystem::with(['features', 'products', 'specifications'])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Systems/Index', [
            'systems' => $systems
        ]);
    }

    public function show($id)
    {
        $system = SolarSystem::with(['features', 'products', 'specifications'])
            ->active()
            ->findOrFail($id);

        return Inertia::render('ProductDetails', [
            'system' => $system,
            'features' => $system->features,
            'products' => $system->products,
            'specifications' => $system->specifications,
        ]);
    }
}
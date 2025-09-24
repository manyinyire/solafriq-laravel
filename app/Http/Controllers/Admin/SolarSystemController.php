<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolarSystem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SolarSystemController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/SolarSystems/Index', [
            'systems' => SolarSystem::all(),
        ]);
    }
}
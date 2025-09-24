<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->withCount(['orders', 'installmentPlans', 'warranties'])
                      ->latest()
                      ->paginate(20);

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->loadCount(['orders', 'installmentPlans', 'warranties', 'warrantyClaims']);
        $user->load(['orders' => function ($query) {
            $query->latest()->take(5);
        }]);

        return response()->json($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'role' => 'sometimes|in:ADMIN,CLIENT',
            'status' => 'sometimes|in:ACTIVE,SUSPENDED,BANNED',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        // Check if user has active orders or installment plans
        if ($user->orders()->whereNotIn('status', ['CANCELLED', 'DELIVERED'])->exists()) {
            return response()->json([
                'message' => 'Cannot delete user with active orders'
            ], 422);
        }

        if ($user->installmentPlans()->where('status', 'ACTIVE')->exists()) {
            return response()->json([
                'message' => 'Cannot delete user with active installment plans'
            ], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function suspend(User $user): JsonResponse
    {
        $user->update(['status' => 'SUSPENDED']);

        return response()->json([
            'message' => 'User suspended successfully',
            'user' => $user
        ]);
    }

    public function activate(User $user): JsonResponse
    {
        $user->update(['status' => 'ACTIVE']);

        return response()->json([
            'message' => 'User activated successfully',
            'user' => $user
        ]);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json(['message' => 'Password reset successfully']);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:suspend,activate,delete',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->get();
        $currentUserId = auth()->id();

        foreach ($users as $user) {
            // Skip current user for destructive actions
            if ($user->id === $currentUserId && in_array($validated['action'], ['suspend', 'delete'])) {
                continue;
            }

            switch ($validated['action']) {
                case 'suspend':
                    $user->update(['status' => 'SUSPENDED']);
                    break;
                case 'activate':
                    $user->update(['status' => 'ACTIVE']);
                    break;
                case 'delete':
                    if (!$user->orders()->whereNotIn('status', ['CANCELLED', 'DELIVERED'])->exists() &&
                        !$user->installmentPlans()->where('status', 'ACTIVE')->exists()) {
                        $user->delete();
                    }
                    break;
            }
        }

        return response()->json(['message' => 'Bulk action completed successfully']);
    }
}
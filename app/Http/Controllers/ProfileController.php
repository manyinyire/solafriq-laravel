<?php

namespace App\Http\Controllers;

use App\Notifications\VerifyEmailChangeNotification;
use App\Models\User;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    protected $imageService;

    public function __construct(ImageOptimizationService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && $user->avatar !== 'avatars/default.png') {
                $this->imageService->deleteImage($user->avatar);
            }

            // Upload and optimize new avatar
            $avatarPath = $this->imageService->uploadAvatar($request->file('avatar'));
            if ($avatarPath) {
                $validated['avatar'] = $avatarPath;
            }
        }

        if ($validated['email'] !== $user->email) {
            $token = Str::random(60);

            $user->update([
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'],
                'phone' => $validated['phone_number'], // Sync both phone columns
                'avatar' => $validated['avatar'] ?? $user->avatar,
                'new_email' => $validated['email'],
                'email_verification_token' => $token,
                'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
            ]);

            $user->notify(new VerifyEmailChangeNotification($token));

            return back()->with('status', 'A verification link has been sent to your new email address.');
        }

        $user->update([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'phone' => $validated['phone_number'], // Sync both phone columns
            'avatar' => $validated['avatar'] ?? $user->avatar,
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function verifyEmailChange(Request $request, $token)
    {
        $user = User::where('email_verification_token', $token)->firstOrFail();

        $user->update([
            'email' => $user->new_email,
            'new_email' => null,
            'email_verification_token' => null,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('profile.show')->with('status', 'Your email address has been updated.');
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The password is incorrect.',
            ]);
        }

        // Log the user out
        Auth::logout();

        // Delete the user account (soft delete if configured)
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been deleted successfully.');
    }
}
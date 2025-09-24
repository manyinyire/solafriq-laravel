<?php

namespace App\Http\Controllers;

use App\Notifications\VerifyEmailChangeNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:255',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if ($validated['email'] !== $user->email) {
            $token = Str::random(60);

            $user->update([
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'],
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
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        return back();
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
}
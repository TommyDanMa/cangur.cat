<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Handle password change for an authenticated user.
 */
class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * - validateWithBag() adds errors to a named bag for the view.
     * - 'current_password' rule verifies the existing password against the logged-in user.
     * - Hash::make() is used before storing (never store plain text).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}

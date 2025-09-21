<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * ProfileController
 *
 * Notes for Flask developers:
 * - $request->user() returns the authenticated user (similar to Flask-Login's current_user).
 * - Validation helpers like validateWithBag() add errors to a named error bag and auto-redirect on failure.
 */
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     *
     * View rendering:
     * - Returns a Blade template with the currently authenticated user.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     *
     * Validation & mass assignment:
     * - ProfileUpdateRequest handles validation rules via FormRequest.
     * - fill() + save() will persist only fillable attributes on the User model.
     * - If email changes, we clear verification timestamp.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     *
     * Authorization/validation:
     * - Requires current_password to match (built-in validation rule).
     * Session handling:
     * - After deleting, we fully log out and invalidate the session + CSRF token.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        // Similar to Flask's session.clear(); also regenerates CSRF token like Flask-WTF would recommend
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

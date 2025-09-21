<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller that asks a logged-in user to re-confirm their password.
 *
 * Similar to Flask workflows where sensitive actions require re-authentication.
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     *
     * - Uses Auth::validate() to check credentials against the web guard without logging in anew.
     * - On success, stores a timestamp in session to mark recent confirmation.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            // Throw a validation error on the 'password' field (auto-redirects back)
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        // Mark time of confirmation; middleware can enforce recency.
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('home', absolute: false));
    }
}

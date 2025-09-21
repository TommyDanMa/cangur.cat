<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Session/auth controller for login/logout.
 *
 * Differences vs Flask:
 * - LoginRequest encapsulates validation and authentication logic; in Flask you'd typically validate form data
 *   and call login_user() from Flask-Login manually.
 * - Session regeneration and CSRF token management are handled by Laravel helpers.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * - $request->authenticate() will validate credentials and log in the user using the configured guard.
     * - On success, regenerate the session ID to prevent fixation and redirect to intended URL.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     *
     * - Logs out via guard, invalidates session, regenerates CSRF token (similar to Flask-Login logout_user + session.clear).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

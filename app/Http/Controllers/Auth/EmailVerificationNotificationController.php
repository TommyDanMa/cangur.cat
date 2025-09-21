<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Trigger an email verification notification for the authenticated user.
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * - If already verified, redirect home.
     * - Otherwise, use Laravel's built-in notification to send the link and flash a status message.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}

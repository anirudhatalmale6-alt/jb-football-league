<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /** Step 1: show the "enter your email" form. */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /** Step 2: email a reset link. Always returns the same neutral message
     *  so we never reveal whether an email is registered or not. */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // The Password broker generates a unique, hashed, single-use token that
        // expires per config/auth.php (60 minutes). We ignore the exact result
        // and always show the same message for security.
        Password::sendResetLink($request->only('email'));

        return back()->with('status', __('app.reset_link_sent'));
    }

    /** Step 3: show the "set a new password" form from the emailed link. */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Step 4: validate the token and store the new password. */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // On success the broker deletes the token (one-time use) so the link
        // can never be reused.
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __('app.password_updated'));
        }

        return back()
            ->withErrors(['email' => __($status)])
            ->onlyInput('email');
    }
}

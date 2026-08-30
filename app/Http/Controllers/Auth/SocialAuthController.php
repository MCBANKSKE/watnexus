<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Spatie\Permission\PermissionRegistrar;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the Google OAuth consent screen.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle the Google OAuth callback.
     *
     * A Google account is matched to an existing user (by `google_id` or by
     * `email`, linking the identities) or registered as a brand-new user.
     * New users have no company yet, so — exactly like RegisterController —
     * they are assigned the `pending_company_setup` role and routed to
     * /company-setup to finish onboarding.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (InvalidStateException $e) {
            // User denied / Google rejected the code.
            return redirect()->route('login')->with('error', 'Google sign-in was cancelled or failed. Please try again.');
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Could not authenticate with Google. Please try again.');
        }

        $email = $googleUser->getEmail();

        if (empty($email)) {
            return redirect()->route('login')->with('error', 'We need your email address to sign in with Google.');
        }

        // Match an existing user by Google ID, otherwise by email (account linking).
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $email)->first();

        if (! $user) {
            // --- New Google sign-up: mirror RegisterController's flow ---
            $name = $googleUser->getName()
                ?? $googleUser->getFirstName()
                ?? Str::before($email, '@');

            $user = DB::transaction(function () use ($email, $name, $googleUser) {
                return User::create([
                    'name' => $name,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Str::random(24), // never used; cast hashes it
                    'provider' => 'google',
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            });

            $user->assignRole('pending_company_setup');

            Auth::login($user);

            return redirect()->route('company.setup');
        }

        // Link the Google identity to a pre-existing password account if needed.
        if ($user->provider !== 'google' || $user->google_id !== $googleUser->getId()) {
            $user->forceFill([
                'provider' => 'google',
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }

        Auth::login($user);

        return $this->redirectPath($user);
    }

    /**
     * Resolve the post-login destination, mirroring LoginController::login()
     * so Google users land in exactly the same places as password users.
     */
    protected function redirectPath(User $user)
    {
        $user->load('roles');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // System super admin — no company required.
        if ($user->hasRole('super_admin')) {
            return redirect()->to('/super-admin');
        }

        // Users without a company must complete onboarding first.
        if (! $user->companies()->exists()) {
            return redirect()->route('company.setup');
        }

        if ($user->is_superadmin) {
            return redirect()->to('/admin');
        }

        if ($user->hasRole('customer')) {
            if (is_null($user->email_verified_at)) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended('/customer');
        }

        return redirect()->intended('/');
    }
}

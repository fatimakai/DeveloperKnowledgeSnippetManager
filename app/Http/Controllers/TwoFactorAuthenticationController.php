<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends Controller
{
    public function store(Request $request, TwoFactorAuthenticationService $twoFactor): RedirectResponse
    {
        $request->validateWithBag('twoFactorSetup', [
            'password' => ['required', 'current_password'],
        ]);

        $codes = $twoFactor->beginSetup($request->user());
        $request->session()->put('two_factor.pending_recovery_codes', $codes);

        return redirect()->route('profile.edit')->with('status', 'two-factor-setup-started');
    }

    public function confirm(Request $request, TwoFactorAuthenticationService $twoFactor): RedirectResponse
    {
        $request->validateWithBag('twoFactorConfirmation', [
            'code' => ['required', 'digits:6'],
        ]);

        if (! $twoFactor->confirm($request->user(), $request->string('code')->toString())) {
            return back()->withErrors([
                'code' => 'The authentication code is invalid or has already been used.',
            ], 'twoFactorConfirmation');
        }

        $codes = $request->session()->pull('two_factor.pending_recovery_codes', []);

        return redirect()->route('profile.edit')
            ->with('status', 'two-factor-enabled')
            ->with('two_factor_recovery_codes', $codes);
    }

    public function recoveryCodes(Request $request, TwoFactorAuthenticationService $twoFactor): RedirectResponse
    {
        $request->validateWithBag('twoFactorRecoveryCodes', [
            'password' => ['required', 'current_password'],
        ]);

        abort_unless($request->user()->twoFactorEnabled(), 409);
        $codes = $twoFactor->regenerateRecoveryCodes($request->user());

        return redirect()->route('profile.edit')
            ->with('status', 'two-factor-recovery-codes-regenerated')
            ->with('two_factor_recovery_codes', $codes);
    }

    public function destroy(Request $request, TwoFactorAuthenticationService $twoFactor): RedirectResponse
    {
        $request->validateWithBag('twoFactorDisable', [
            'password' => ['required', 'current_password'],
        ]);

        $twoFactor->disable($request->user());
        $request->session()->forget('two_factor.pending_recovery_codes');

        return redirect()->route('profile.edit')->with('status', 'two-factor-disabled');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'estatus' => session('estatus'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'telefono' => ['required', 'string', 'regex:/^[0-9+\s\-]{8,20}$/'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $estatus = Password::sendResetLink(
            $request->only('telefono')
        );

        if ($estatus == Password::RESET_LINK_SENT) {
            return back()->with('estatus', __($estatus));
        }

        throw ValidationException::withMessages([
            'telefono' => [trans($estatus)],
        ]);
    }
}

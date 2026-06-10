<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pais;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'paises' => Pais::activos()->get(['code', 'name', 'dial']),
            'turnstileSiteKey' => config('services.turnstile.site_key'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'telefono' => 'required|string|regex:/^[0-9+\s\-]{8,20}$/|unique:'.User::class,
            'pais' => 'nullable|string|max:10',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'cf_turnstile_response' => 'required|string',
        ]);

        $turnstileVerify = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => config('services.turnstile.secret_key'),
            'response' => $request->cf_turnstile_response,
            'remoteip' => $request->ip(),
        ]);

        if (! ($turnstileVerify->json('success') === true)) {
            throw ValidationException::withMessages([
                'cf_turnstile_response' => 'La verificación de seguridad falló. Intenta de nuevo.',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'telefono' => $request->telefono,
            'pais' => $request->pais,
            'password' => Hash::make($request->password),
        ]);

        $user->markEmailAsVerified();

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}

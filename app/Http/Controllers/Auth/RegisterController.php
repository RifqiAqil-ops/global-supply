<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true,
        ]);

        // Create default user preferences
        UserPreference::create([
            'user_id' => $user->id,
            'dashboard_layout' => 'default',
            'default_currency' => 'USD',
            'items_per_page' => 25,
            'theme' => 'dark', // Modern Premium Default
            'email_notifications' => true,
            'alert_notifications' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Log actions
        log_activity('register', 'User registered and logged in', $user);
        Log::info("New user registered: {$user->email} from IP " . $request->ip());

        return redirect()->route('user.dashboard');
    }
}

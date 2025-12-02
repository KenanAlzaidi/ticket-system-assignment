<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class AuthController
 *
 * Handles authentication for Admin users.
 * Uses standard Laravel Auth facade but customized for the admin portal flow.
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * Display the login form.
     *
     * @return View
     */
    public function showLoginForm(): View
    {
        return view('admin.login');
    }

    /**
     * Handle an incoming login request.
     *
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        // Attempt to log the user in
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.tickets.index');
        }

        // If unsuccessful, redirect back with errors
        return back()->withErrors([
            'password' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}


<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);


        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Please fix the validation errors.', 'errors' => $validator->errors(),], 422);
        }


        $credentials = [
            'email' => $request->email,
            'password' => $request->password,

        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();
            $user = Auth::user();
            $redirect = null;

            if ($user->isSuperAdmin()) {
                $redirect = route('superadmin.dashboard');
            } elseif ($user->isAdmin()) {
                $redirect = route('admin.dashboard');
            } elseif ($user->isMember()) {
                $redirect = route('member.dashboard');
            }

            if (!$redirect) {

                Auth::logout();

                $request->session()->invalidate();

                $request->session()->regenerateToken();

                return response()->json([
                    'success' => false,
                    'message' => 'Your account role is not configured.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => $redirect,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password.',

        ], 401);
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
            'redirect' => route('login'),

        ]);
    }
}

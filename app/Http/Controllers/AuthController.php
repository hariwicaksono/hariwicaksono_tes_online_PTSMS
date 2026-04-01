<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json(['token' => $token]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Cari user berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        // Cek jika user tidak ditemukan atau password salah
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            // Gagal login
            \App\Models\ActivityLog::create([
                'user_id'     => null,
                'action'      => 'login_failed',
                'module'      => 'Auth',
                'description' => "Login gagal untuk {$request->email}: Unauthorized",
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Cek status user
        if ($user->status != 1) {
            // Gagal login
            \App\Models\ActivityLog::create([
                'user_id'     => null,
                'action'      => 'login_failed',
                'module'      => 'Auth',
                'description' => "Login gagal untuk {$request->email}: Akun Anda belum aktif",
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            return response()->json(['error' => 'Akun Anda belum aktif'], 403);
        }

        // Jika semua valid → generate token
        $token = JWTAuth::fromUser($user);

        // Ambil daftar permissions dari roles user
        $permissions = $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap->permissions
            ->pluck('name')
            ->unique()
            ->values(); // ['user.view', 'user.create', ...]

        // Ambil daftar role
        $roles = $user->roles->pluck('name'); // ['admin']

        // Berhasil login
        \App\Models\ActivityLog::create([
            'user_id'     => $user->id,
            'action'      => 'login',
            'module'      => 'Auth',
            'description' => "Login sukses oleh {$user->name}",
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return response()->json([
            'token' => $token,
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'roles'      => $roles,
                'permissions' => $permissions,
            ],
            'permissions' => $permissions,
        ]);
    }

    public function me()
    {
        $user = auth()->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->roles()
                ->with('permissions')
                ->get()
                ->flatMap->permissions
                ->pluck('name')
                ->unique()
                ->values()
        ]);
    }

    public function logout()
    {
        $user = auth()->user();
        auth()->logout();

        // Logout Berhasil
        \App\Models\ActivityLog::create([
            'user_id'     => $user->id,
            'action'      => 'logout',
            'module'      => 'Auth',
            'description' => "Logout oleh {$user->name}",
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        //Log::info('🚨 REFRESH REQUEST HEADER:', [request()->header('Authorization')]);

        try {
            $token = JWTAuth::getToken(); // Ambil token dari Authorization header
            //Log::info('🔑 Token Before Refresh:', [$token]);

            $newToken = JWTAuth::refresh($token); // Refresh token
            //Log::info('✅ Token After Refresh:', [$newToken]);

            return response()->json(['token' => $newToken]);
        } catch (JWTException $e) {
            //Log::error('❌ Refresh Error:', [
            //'msg' => $e->getMessage(),
            //'token' => request()->header('Authorization')
            //]);

            return response()->json([
                'error' => 'Could not refresh token',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Link reset password telah dikirim ke email kamu.']);
        }

        return response()->json(['message' => 'Gagal mengirim link reset.'], 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
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

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password berhasil direset.']);
        }

        return response()->json(['message' => __($status)], 500);
    }
}

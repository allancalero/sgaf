<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        \Log::info('Login attempt', ['request' => $request->all()]);
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Restore flexible login (Email OR Username)
        $identifier = $request->email;
        $user = User::where('email', $identifier)
            ->orWhere('nombre', $identifier)
            ->orWhereRaw("UPPER(CONCAT(LEFT(nombre, 1), apellido)) = ?", [strtoupper($identifier)])
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
             \Log::warning('Login failed: invalid credentials', ['identifier' => $identifier]);
             throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }
        \Log::info('Generating JWT token for user', ['user_id' => $user->id]);
        try {
            $token = auth('api_jwt')->login($user);
            \Log::info('JWT token generated successfully', ['token_length' => strlen($token)]);
        } catch (\Exception $e) {
            \Log::error('Error generating JWT token', ['error' => $e->getMessage()]);
            throw $e;
        }
        
        return $this->respondWithToken($token, $user);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api_jwt')->refresh(), auth('api_jwt')->user());
    }

    public function logout(Request $request)
    {
        auth('api_jwt')->logout();

        return response()->json(['message' => 'Successfully logged out'])
            ->withCookie(cookie()->forget('jwt_token'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',             // must be at least 8 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        $user = auth('api_jwt')->user();

        if (!Hash::check($request->current_password, $user->password)) {
             throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'must_change_password' => false
        ]);

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }

    protected function respondWithToken($token, $user)
    {
        $cookie = cookie(
            'jwt_token',
            $token,
            60 * 24, // 1 day minutes (adjust as needed, library defaults usually 60 min)
            null,
            null,
            app()->environment('production'), // secure only in production
            true, // httpOnly
            false,
            'Lax'
        );

        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api_jwt')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'email' => $user->email,
                'role' => $user->rol,
                'must_change_password' => $user->must_change_password,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
        ];
        
        return response()->json($data)->withCookie($cookie);
    }
}

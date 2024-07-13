<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rafistoleur;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Enregistrer un utilisateur (client ou rafistoleur)
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5|confirmed',
            'status' => 'required|string|in:client,rafistoleur',
        ]);

        // Créer l'utilisateur de base
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        // Créer le client ou le rafistoleur en fonction du statut
        if ($request->status === 'client') {
            Client::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'tel' => $request->tel,
                'location' => $request->location,
                'password' => Hash::make($request->password),
            ]);
        } elseif ($request->status === 'rafistoleur') {
            Rafistoleur::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'tel' => $request->tel,
                'location' => $request->location,
                'password' => Hash::make($request->password),
            ]);
        }

        // Générer un token JWT pour l'utilisateur nouvellement créé
        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

    // Connecter un utilisateur
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(compact('token'));
    }

    // Récupérer les informations de l'utilisateur actuellement connecté
    public function me()
    {
        return response()->json(Auth::user());
    }

    // Déconnecter l'utilisateur
    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    // Rafraîchir le token JWT
    public function refresh()
    {
        $token = Auth::refresh();
        return response()->json(compact('token'));
    }
}

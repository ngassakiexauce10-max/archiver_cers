<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/register',
        summary: 'Créer un compte',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nom', 'prenom', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'nom', type: 'string', example: 'Test'),
                    new OA\Property(property: 'prenom', type: 'string', example: 'Exauce'),
                    new OA\Property(property: 'email', type: 'string', example: 'test@mail.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Compte créé, token retourné'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom'      => ['required', 'string', 'max:255'],
            'prenom'   => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create($validated);

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    #[OA\Post(
        path: '/login',
        summary: 'Se connecter et obtenir un token',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'test@mail.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Connexion réussie, token retourné'),
            new OA\Response(response: 422, description: 'Identifiants incorrects'),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Se déconnecter (supprime le token courant)',
        security: [['bearerAuth' => []]],
        tags: ['Authentification'],
        responses: [
            new OA\Response(response: 200, description: 'Déconnexion réussie'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    #[OA\Get(
        path: '/me',
        summary: "Infos de l'utilisateur connecté",
        security: [['bearerAuth' => []]],
        tags: ['Authentification'],
        responses: [
            new OA\Response(response: 200, description: "L'utilisateur connecté"),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}

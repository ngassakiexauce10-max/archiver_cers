<?php

namespace App\Http\Controllers;

use App\Models\Cer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CerController extends Controller
{
    #[OA\Get(
        path: '/cers',
        summary: "Lister les CER de l'utilisateur connecté",
        security: [['bearerAuth' => []]],
        tags: ['CER'],
        responses: [
            new OA\Response(response: 200, description: 'Liste des CER'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->cers()->latest()->get());
    }

    #[OA\Post(
        path: '/cers',
        summary: 'Créer un CER',
        security: [['bearerAuth' => []]],
        tags: ['CER'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'path'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'CER 1'),
                    new OA\Property(property: 'path', type: 'string', example: '/archives/cer1.pdf'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'CER créé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'path'  => ['required', 'string'],
        ]);

        $cer = $request->user()->cers()->create($validated);

        return response()->json($cer, 201);
    }

    #[OA\Get(
        path: '/cers/{id}',
        summary: 'Afficher un CER',
        security: [['bearerAuth' => []]],
        tags: ['CER'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Le CER'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Accès interdit'),
            new OA\Response(response: 404, description: 'CER introuvable'),
        ]
    )]
    public function show(Request $request, Cer $cer): JsonResponse
    {
        $this->ensureOwner($request, $cer);

        return response()->json($cer);
    }

    #[OA\Put(
        path: '/cers/{id}',
        summary: 'Modifier un CER',
        security: [['bearerAuth' => []]],
        tags: ['CER'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'path'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'CER modifié'),
                    new OA\Property(property: 'path', type: 'string', example: '/archives/cer1-v2.pdf'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'CER mis à jour'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Accès interdit'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(Request $request, Cer $cer): JsonResponse
    {
        $this->ensureOwner($request, $cer);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'path'  => ['required', 'string'],
        ]);

        $cer->update($validated);

        return response()->json($cer);
    }

    #[OA\Delete(
        path: '/cers/{id}',
        summary: 'Supprimer un CER',
        security: [['bearerAuth' => []]],
        tags: ['CER'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'CER supprimé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Accès interdit'),
        ]
    )]
    public function destroy(Request $request, Cer $cer): JsonResponse
    {
        $this->ensureOwner($request, $cer);

        $cer->delete();

        return response()->json(null, 204);
    }

    private function ensureOwner(Request $request, Cer $cer): void
    {
        abort_unless($cer->user_id === $request->user()->id, 403, 'Accès interdit.');
    }
}

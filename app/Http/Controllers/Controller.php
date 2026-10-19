<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Archiva - Gestion des CER',
    version: '1.0.0',
    description: 'API REST pour archiver des CER, protégée par Laravel Sanctum.'
)]
#[OA\Server(url: 'http://127.0.0.1:8000/api', description: 'Serveur local')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum'
)]
abstract class Controller
{
    //
}

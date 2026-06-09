<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'IFOD SIRH API',
    description: "API REST du Système d'Information des Ressources Humaines (SIRH) IFOD.",
    contact: new OA\Contact(name: 'IFOD', email: 'support@ifod.local')
)]
#[OA\Server(url: 'http://localhost:8000', description: 'Serveur local')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Tag(name: 'Auth', description: 'Authentification JWT')]
#[OA\Tag(name: 'Dashboard', description: 'Tableau de bord exécutif RH')]
#[OA\Tag(name: 'Employees', description: 'Gestion administrative du personnel (Module 1)')]
#[OA\Tag(name: 'Organisation', description: 'Départements, services et fonctions')]
abstract class Controller
{
    //
}

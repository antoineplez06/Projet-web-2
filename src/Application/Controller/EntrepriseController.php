<?php

namespace App\Application\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class EntrepriseController
{

    public function liste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        $entreprises = [
            [
                "id" => 1,
                "nom" => "TechBaguette",
                "secteur" => "Paris"
            ],
            [
                "id" => 2,
                "nom" => "ÉcoLogis",
                "secteur" => "Lyon"
            ],
            [
                "id" => 3,
                "nom" => "SantéPlus Solutions",
                "secteur" => "Marseille"
            ],
            [
                "id" => 4,
                "nom" => "Transport Express Sud",
                "secteur" => "Toulouse"
            ],
            [
                "id" => 5,
                "nom" => "Brasserie des Flandres",
                "secteur" => "Lille"
            ],
            [
                "id" => 6,
                "nom" => "Vody",
                "secteur" => "Bordeaux"
            ],
            [
                "id" => 7,
                "nom" => "Ateliers Mécaniques Bretons",
                "secteur" => "Nantes"
            ],
            [
                "id" => 8,
                "nom" => "PAKPAK",
                "secteur" => "Grenoble"
            ],
            [
                "id" => 9,
                "nom" => "StartUp Vision",
                "secteur" => "Strasbourg"
            ],
            [
                "id" => 10,
                "nom" => "Cosmétiques Azur",
                "secteur" => "Nice"
            ],

            ['id' => 11, 'nom' => 'CESI', 'secteur' => 'IA', 'statut' => 'Actif'],
            ['id' => 12, 'nom' => 'GreenLeaf', 'secteur' => 'Écologie', 'statut' => 'En attente'],
            ['id' => 13, 'nom' => 'CyberShield', 'secteur' => 'Sécurité', 'statut' => 'Actif'],
            ['id' => 14, 'nom' => 'BlueHorizon', 'secteur' => 'Logistique', 'statut' => 'Inactif'],
        ];

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        if ($page < 1)
            $page = 1;

        $perPage = 5; // 5 par page
        $totalItems = count($entreprises);
        $nombrePages = ceil($totalItems / $perPage);

        $offset = ($page - 1) * $perPage;
        $entreprisesAffichees = array_slice($entreprises, $offset, $perPage);

        return $view->render($response, 'liste-entreprises.html.twig', [
            'entreprises' => $entreprisesAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages
        ]);
    }


    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        $parsedBody = $request->getParsedBody();
        //var_dump($parsedBody); 

        //Vérifier l'id
        $success = false;
        if ($request->getMethod() === 'POST') {
            //Ici, on ajouterait l'entreprise à la base de données
            $success = true;
        }

        return $view->render($response, 'ajout-entreprise.html.twig', [
            "nom" => $parsedBody['nom'] ?? '',
            "success" => $success
        ]);
    }


    public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'entreprise_formulaire.twig', [

        ]);
    }
}
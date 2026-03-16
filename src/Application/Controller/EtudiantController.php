<?php

namespace App\Application\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class EtudiantController
{

    public function liste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        $etudiants = [
            [
                "id" => 1,
                "prenom" => "Lucas",
                "nom" => "Dubois",
                "mail" => "lucas.dubois@ecole.fr",
                "campus" => "Paris",
                "promo" => 2026
            ],
            [
                "id" => 2,
                "prenom" => "Emma",
                "nom" => "Martin",
                "mail" => "emma.martin@ecole.fr",
                "campus" => "Lyon",
                "promo" => 2027
            ],
            [
                "id" => 3,
                "prenom" => "Hugo",
                "nom" => "Bernard",
                "mail" => "hugo.bernard@ecole.fr",
                "campus" => "Bordeaux",
                "promo" => 2026
            ],
            [
                "id" => 4,
                "prenom" => "Chloé",
                "nom" => "Thomas",
                "mail" => "chloe.thomas@ecole.fr",
                "campus" => "Nantes",
                "promo" => 2028
            ],
            [
                "id" => 5,
                "prenom" => "Maxime",
                "nom" => "Robert",
                "mail" => "maxime.robert@ecole.fr",
                "campus" => "Lille",
                "promo" => 2027
            ],
            [
                "id" => 6,
                "prenom" => "Léa",
                "nom" => "Richard",
                "mail" => "lea.richard@ecole.fr",
                "campus" => "Strasbourg",
                "promo" => 2026
            ],
            [
                "id" => 7,
                "prenom" => "Arthur",
                "nom" => "Petit",
                "mail" => "arthur.petit@ecole.fr",
                "campus" => "Toulouse",
                "promo" => 2029
            ],
            [
                "id" => 8,
                "prenom" => "Camille",
                "nom" => "Durand",
                "mail" => "camille.durand@ecole.fr",
                "campus" => "Marseille",
                "promo" => 2028
            ],
            [
                "id" => 9,
                "prenom" => "Antoine",
                "nom" => "Laurent",
                "mail" => "antoine.laurent@ecole.fr",
                "campus" => "Nice",
                "promo" => 2027
            ],
            [
                "id" => 10,
                "prenom" => "Julie",
                "nom" => "Simon",
                "mail" => "julie.simon@ecole.fr",
                "campus" => "Montpellier",
                "promo" => 2026
            ],
            [
                "id" => 11,
                "prenom" => "Paul",
                "nom" => "Michel",
                "mail" => "paul.michel@ecole.fr",
                "campus" => "Paris",
                "promo" => 2028
            ],
            [
                "id" => 12,
                "prenom" => "Sarah",
                "nom" => "Lefebvre",
                "mail" => "sarah.lefebvre@ecole.fr",
                "campus" => "Lyon",
                "promo" => 2029
            ],
            [
                "id" => 13,
                "prenom" => "Tom",
                "nom" => "Leroy",
                "mail" => "tom.leroy@ecole.fr",
                "campus" => "Bordeaux",
                "promo" => 2027
            ],
            [
                "id" => 14,
                "prenom" => "Laura",
                "nom" => "Roux",
                "mail" => "laura.roux@ecole.fr",
                "campus" => "Lille",
                "promo" => 2026
            ],
            [
                "id" => 15,
                "prenom" => "Nicolas",
                "nom" => "David",
                "mail" => "nicolas.david@ecole.fr",
                "campus" => "Nantes",
                "promo" => 2028
            ],
            [
                "id" => 16,
                "prenom" => "Marie",
                "nom" => "Bertrand",
                "mail" => "marie.bertrand@ecole.fr",
                "campus" => "Strasbourg",
                "promo" => 2027
            ],
            [
                "id" => 17,
                "prenom" => "Alexandre",
                "nom" => "Moreau",
                "mail" => "alexandre.moreau@ecole.fr",
                "campus" => "Toulouse",
                "promo" => 2029
            ],
            [
                "id" => 18,
                "prenom" => "Sophie",
                "nom" => "Fournier",
                "mail" => "sophie.fournier@ecole.fr",
                "campus" => "Marseille",
                "promo" => 2026
            ],
            [
                "id" => 19,
                "prenom" => "Clément",
                "nom" => "Girard",
                "mail" => "clement.girard@ecole.fr",
                "campus" => "Paris",
                "promo" => 2028
            ],
            [
                "id" => 20,
                "prenom" => "Manon",
                "nom" => "Bonnet",
                "mail" => "manon.bonnet@ecole.fr",
                "campus" => "Lyon",
                "promo" => 2027
            ]
        ];

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        if ($page < 1)
            $page = 1;

        $perPage = 5; // On va dire 5 par page pour tester
        $totalItems = count($etudiants);
        $nombrePages = ceil($totalItems / $perPage);

        $offset = ($page - 1) * $perPage;
        $etudiantAffichees = array_slice($etudiants, $offset, $perPage);

        return $view->render($response, 'liste-etudiant.html.twig', [
            'etudiant' => $etudiantAffichees,
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

        return $view->render($response, 'ajout-etudiant.html.twig', [
            "nom" => $parsedBody['nom'] ?? '',
            "success" => $success
        ]);
    }


    public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'etudiant-formulaire.twig', [

        ]);
    }



}
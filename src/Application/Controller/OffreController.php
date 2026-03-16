<?php

namespace App\Application\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;



class OffreController
{

    public function liste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        // Simulation de ta base de données d'offres
        $offres = [
            [
                'id' => 1,
                'nom' => 'Développeur Web Fullstack',
                'entreprise' => 'TechBaguette',
                'lieu' => 'Paris',
                'date_publication' => '15/03/2026',
                'candidats' => 12,
                'description' => 'Nous cherchons un développeur passionné...',
                'competences' => 'PHP, Symfony, JS, Twig',
                'remuneration' => '2000€ brut/mois'
            ],
            [
                'id' => 2,
                'nom' => 'Designer UX/UI',
                'entreprise' => 'ÉcoLogis',
                'lieu' => 'Lyon',
                'date_publication' => '10/03/2026',
                'candidats' => 5,
                'description' => 'Rejoignez notre équipe créative...',
                'competences' => 'Figma, Adobe XD, HTML/CSS',
                'remuneration' => '1800€ brut/mois'
            ],
            [
                'id' => 3,
                'nom' => 'Administrateur Systèmes',
                'entreprise' => 'CyberShield',
                'lieu' => 'Toulouse',
                'date_publication' => '12/03/2026',
                'candidats' => 3,
                'description' => 'Gestion de notre infrastructure cloud.',
                'competences' => 'Linux, Docker, AWS',
                'remuneration' => '2500€ brut/mois'
            ],
            [
                'id' => 4,
                'nom' => 'Chef de Projet IT',
                'entreprise' => 'SantéPlus Solutions',
                'lieu' => 'Marseille',
                'date_publication' => '05/03/2026',
                'candidats' => 20,
                'description' => 'Pilotage des projets e-santé.',
                'competences' => 'Agile, Scrum, Jira',
                'remuneration' => '2800€ brut/mois'
            ],
            [
                'id' => 5,
                'nom' => 'Data Analyst',
                'entreprise' => 'StartUp Vision',
                'lieu' => 'Strasbourg',
                'date_publication' => '14/03/2026',
                'candidats' => 8,
                'description' => 'Analyse des données utilisateurs.',
                'competences' => 'Python, SQL, Tableau',
                'remuneration' => '2200€ brut/mois'
            ],
            [
                'id' => 6,
                'nom' => 'Technicien Support',
                'entreprise' => 'TechBaguette',
                'lieu' => 'Paris',
                'date_publication' => '16/03/2026',
                'candidats' => 15,
                'description' => 'Support client de niveau 1 et 2.',
                'competences' => 'Windows, Réseaux, Ticketing',
                'remuneration' => '1700€ brut/mois'
            ]
        ];

        // Logique de pagination identique à ton EntrepriseController
        $page = isset($args['page']) ? (int) $args['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        $perPage = 5; // 5 offres par page
        $totalItems = count($offres);
        $nombrePages = ceil($totalItems / $perPage);

        $offset = ($page - 1) * $perPage;
        $offresAffichees = array_slice($offres, $offset, $perPage);

        // Assure-toi que le nom du template correspond à ton fichier twig
        return $view->render($response, 'Offres.html.twig', [
            'offres' => $offresAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages
        ]);
    }

    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $parsedBody = $request->getParsedBody();

        $success = false;

        // Si le formulaire d'ajout (la modale) a été soumis en POST
        if ($request->getMethod() === 'POST') {
            // Ici, tu feras ton $db->insert() avec PDO ou ton ORM plus tard
            // Ex: $titre = $parsedBody['titre'] ?? '';
            // Ex: $entreprise = $parsedBody['entreprise'] ?? '';

            $success = true;

            // ASTUCE SLIM : Après un ajout via POST, on redirige souvent vers la liste 
            // pour éviter que l'utilisateur renvoie le formulaire en faisant F5
            // return $response->withHeader('Location', '/offres')->withStatus(302);
        }

        // Si tu as gardé la modale sur la même page que la liste, tu pourrais rediriger.
        // Si tu as une page dédiée à l'ajout (comme pour Entreprise), on affiche la vue d'ajout :
        return $view->render($response, 'ajout-offre.html.twig', [
            "titre" => $parsedBody['titre'] ?? '',
            "success" => $success
        ]);
    }

    public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'offre_formulaire.html.twig', [
            // Données pour l'édition de l'offre
        ]);
    }
}

<?php

namespace App\Application\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;



class OffreController
{
    public function ajoute(ServerRequestInterface $request, ResponseInterface $response)
{
       $view = Twig::fromRequest($request);
       return $view->render($response, 'ajout-offre.html.twig');
    

}
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

        // ==========================================
        // 1. RÉCUPÉRATION DES FILTRES DEPUIS L'URL (GET)
        // ==========================================
        $params = $request->getQueryParams();

        $search = $params['search'] ?? '';
        $lieu = $params['lieu'] ?? '';
        $candidatsMax = $params['candidats_max'] ?? '';

        // ==========================================
        // 2. APPLICATION DES FILTRES
        // ==========================================
        if (!empty($search) || !empty($lieu) || !empty($candidatsMax)) {

            $offres = array_filter($offres, function ($offre) use ($search, $lieu, $candidatsMax) {
                $match = true;

                // Recherche texte (nom de l'offre ou entreprise)
                if (!empty($search)) {
                    $searchLower = strtolower($search);
                    $nomMatch = strpos(strtolower($offre['nom']), $searchLower) !== false;
                    $entrepriseMatch = strpos(strtolower($offre['entreprise']), $searchLower) !== false;

                    if (!$nomMatch && !$entrepriseMatch) {
                        $match = false;
                    }
                }

                // Filtre par Lieu
                if (!empty($lieu) && strtolower($offre['lieu']) !== strtolower($lieu)) {
                    $match = false;
                }

                // Filtre par Candidats maximum
                if (!empty($candidatsMax) && $offre['candidats'] > (int) $candidatsMax) {
                    $match = false;
                }

                return $match;
            });

            // On réindexe le tableau de 0 à X après avoir supprimé les éléments filtrés
            $offres = array_values($offres);
        }

        // ==========================================
        // 3. PAGINATION
        // ==========================================
        $page = isset($args['page']) ? (int) $args['page'] : 1;
        if ($page < 1)
            $page = 1;

        $perPage = 5; // On va dire 5 par page pour tester
        $totalItems = count($offres);
        $nombrePages = ceil($totalItems / $perPage);

        $offset = ($page - 1) * $perPage;
        $offresAffichees = array_slice($offres, $offset, $perPage);

        return $view->render($response, 'Offres.html.twig', [
            'offres' => $offresAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages,
            'filtres' => $params,
        ]);

    }
    
    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $parsedBody = $request->getParsedBody();

        $success = false;

        // Si la modale d'ajout a été soumise en POST
        if ($request->getMethod() === 'POST') {
            // C'est ici que tu feras ta requête SQL pour ajouter l'offre en base de données
            // Exemple : 
            // $titre = $parsedBody['titre'];
            // $entreprise = $parsedBody['entreprise'];

            $success = true;

            // Décommente la ligne ci-dessous si tu veux rediriger vers la liste après l'ajout
            // return $response->withHeader('Location', '/offres')->withStatus(302);
        }

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

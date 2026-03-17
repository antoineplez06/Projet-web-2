<?php

namespace App\Application\Controller;

use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class EntrepriseController
{
    private EntityManager $entityManager;

    // Le container de Slim va automatiquement injecter l'EntityManager ici
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function liste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        // --- TEST DE CONNEXION RAPIDE ---
        try {
            $dbName = $this->entityManager->getConnection()->getDatabase();
            // Si on arrive ici, la connexion fonctionne !
            // Tu peux décommenter la ligne suivante pour vérifier :
            // die("Connexion réussie à la base : " . $dbName);
        } catch (\Exception $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
        // --------------------------------

        // Ton code de pagination statique reste identique ci-dessous...
        $entreprises = [
            ["id" => 1, "nom" => "TechBaguette", "secteur" => "Paris"],
            // ... (tes données)
        ];

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;
        $entreprisesAffichees = array_slice($entreprises, $offset, $perPage);

        return $view->render($response, 'liste-entreprises.html.twig', [
            'entreprises' => $entreprisesAffichees,
            'pageActuelle' => $page,
            'nombrePages' => ceil(count($entreprises) / $perPage)
        ]);
    }

    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $parsedBody = $request->getParsedBody();
        $success = false;

        if ($request->getMethod() === 'POST') {
            // Ici, tu pourrais tester une insertion réelle plus tard :
            // $entreprise = new Entreprise();
            // $entreprise->setNom($parsedBody['nom']);
            // $this->entityManager->persist($entreprise);
            // $this->entityManager->flush();
            $success = true;
        }

        return $view->render($response, 'ajout-entreprise.html.twig', [
            "nom" => $parsedBody['nom'] ?? '',
            "success" => $success
        ]);
    }
}
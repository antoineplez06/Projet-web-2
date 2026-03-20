<?php

namespace App\Application\Controller;

use App\Application\Domain\Offre;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use DateTimeImmutable;

class OffreController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function liste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        $params = $request->getQueryParams();

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(Offre::class);

        // Récupération des offres avec pagination (comme dans EntrepriseController)
        $offresAffichees = $repository->findBy(
            [],
            ['idOffre' => 'DESC'], // Tri par ID décroissant
            $perPage,
            $offset
        );

        $totalOffres = $repository->count([]);
        $nombrePages = ceil($totalOffres / $perPage);

        return $view->render($response, 'Offres.html.twig', [
            'offres' => $offresAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages,
            'filtres'        => $params
        ]);
    }

    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $parsedBody = $request->getParsedBody();
        $success = false;

        if ($request->getMethod() === 'POST') {
            // Création de l'entité Offre avec les données du formulaire
            // On respecte les champs du screenshot
            $nouvelleOffre = new Offre(
                $parsedBody['nom'] ?? '',
                $parsedBody['duree'] ?? '',
                $parsedBody['exigence_etude'] ?? '',
                $parsedBody['entreprise'] ?? '',
                new DateTimeImmutable($parsedBody['date']),
                (float) ($parsedBody['remuneration'] ?? 0),
                $parsedBody['description'] ?? '',
                $parsedBody['presentiel_ou_distanciel'] ?? ''
            );

            $this->entityManager->persist($nouvelleOffre);
            $this->entityManager->flush();

            $success = true;
        }

        return $view->render($response, 'ajout-offre.html.twig', [
            'success' => $success
        ]);
    }

    public function afficherFormulairePostuler(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $idOffre = $args['id'];

        return $view->render($response, 'postuler.html.twig', [
            'id' => $idOffre
        ]);
    }
}
<?php

namespace App\Application\Controller;
use App\Application\Domain\Entreprise;

use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class EntrepriseController
{
    private EntityManager $entityManager;


    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function liste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
    $page = isset($args['page']) ? (int) $args['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    
    $repository = $this->entityManager->getRepository(Entreprise::class);


    $entreprisesAffichees = $repository->findBy(
        [],                         
        ['id' => 'DESC'],    
        $perPage,                   
        $offset                     
    );


    $totalEntreprises = $repository->count([]);
    $nombrePages = ceil($totalEntreprises / $perPage);

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
    $success = false;

    if ($request->getMethod() === 'POST') {

        $nouvelleEntreprise = new Entreprise(
            $parsedBody['nom'] ?? '',
            $parsedBody['adresse'] ?? '',
            $parsedBody['siret'] ?? '',
            $parsedBody['domaine'] ?? '',
            $parsedBody['taille'] ?? '',
        );


        $this->entityManager->persist($nouvelleEntreprise);
        $this->entityManager->flush();

        $success = true;
    }

    return $view->render($response, 'ajout-entreprise.html.twig', [
        'success' => $success
    ]);
    }

    public function listean(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
    $page = isset($args['page']) ? (int) $args['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    
    $repository = $this->entityManager->getRepository(Entreprise::class);


    $entreprisesAffichees = $repository->findBy(
        [],                         
        ['id' => 'DESC'],    
        $perPage,                   
        $offset                     
    );


    $totalEntreprises = $repository->count([]);
    $nombrePages = ceil($totalEntreprises / $perPage);

    return $view->render($response, 'Entreprises-an.html.twig', [
        'entreprises' => $entreprisesAffichees,
        'pageActuelle' => $page,
        'nombrePages' => $nombrePages
    ]);
    }
}
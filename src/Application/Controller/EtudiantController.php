<?php

namespace App\Application\Controller;
use App\Application\Domain\User;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Doctrine\ORM\EntityManager;
use Slim\Views\Twig;

class EtudiantController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function liste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

       
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findAll();

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        if ($page < 1)
            $page = 1;

        $perPage = 5;
        $totalItems = count($users);
        $nombrePages = ceil($totalItems / $perPage);

        $offset = ($page - 1) * $perPage;
        $etudiantAffichees = array_slice($users, $offset, $perPage);

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

        $success = false;
        if ($request->getMethod() === 'POST') {

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
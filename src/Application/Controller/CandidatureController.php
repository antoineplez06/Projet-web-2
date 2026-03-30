<?php

namespace App\Application\Controller;

use App\Application\Domain\Candidature;
use App\Application\Domain\Offre;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class CandidatureController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function list(Request $request, Response $response): Response {
        $view = Twig::fromRequest($request);
        $candidatures = $this->entityManager->getRepository(Candidature::class)->findAll();
        return $view->render($response, 'candidature/liste.html.twig', [
            'candidatures' => $candidatures
        ]);
    }

    public function add(Request $request, Response $response, array $args): Response {
        $idOffre = (int)$args['id'];
        
        $offre = $this->entityManager->getRepository(Offre::class)->find($idOffre);

        if ($offre) {
            $candidature = new Candidature($offre);
            $this->entityManager->persist($candidature);
            $this->entityManager->flush();
        }
        return $response->withHeader('Location', '/candidatures')->withStatus(302);
    }
}
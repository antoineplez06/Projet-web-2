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

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function list(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $userConnecte = $request->getAttribute('user');

        if (!$userConnecte) {
            return $response->withHeader('Location', '/connexion')->withStatus(302);
        }

        $role = strtolower($userConnecte->getRoleValue());


        $queryBuilder = $this->entityManager->getRepository(Candidature::class)
            ->createQueryBuilder('c')
            ->join('c.etudiant', 'e'); 
        if ($role === 'etudiant') {
            $queryBuilder->where('c.etudiant = :etudiant')->setParameter('etudiant', $userConnecte);
        } elseif ($role === 'pilote') {
            $campusPilote = $userConnecte->getCampus();
            if ($campusPilote) {
                $queryBuilder->where('e.campus = :campus')
                    ->setParameter('campus', $campusPilote);
            }
        }

        $candidatures = $queryBuilder->getQuery()->getResult();

        return $view->render($response, 'candidature/liste.html.twig', [
            'candidatures' => $candidatures,
            'userRole' => $role 
        ]);
    }

    public function add(Request $request, Response $response, array $args): Response
    {
        $idOffre = (int)$args['id'];
        $userConnecte = $request->getAttribute('user');
        $offre = $this->entityManager->getRepository(Offre::class)->find($idOffre);

        if ($offre && $userConnecte) {
            $candidature = new Candidature($offre, $userConnecte);
            $this->entityManager->persist($candidature);
            $this->entityManager->flush();
        }
        return $response->withHeader('Location', '/candidatures')->withStatus(302);
    }
}

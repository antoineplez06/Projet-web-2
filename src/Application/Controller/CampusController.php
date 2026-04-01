<?php

namespace App\Application\Controller;

use App\Application\Domain\Campus;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class CampusController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function listeAdmin(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $queryParams = $request->getQueryParams();
        $search = $queryParams['q'] ?? null;

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(Campus::class);
        $queryBuilder = $repository->createQueryBuilder('c');

        // Si une recherche est effectuée (sur la ville)
        if ($search) {
            $queryBuilder->where('c.ville LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->orderBy('c.id_campus', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $campusAffiches = $queryBuilder->getQuery()->getResult();

        // Calcul du total pour la pagination
        $countBuilder = $repository->createQueryBuilder('c')
            ->select('count(c.id_campus)');
        if ($search) {
            $countBuilder->where('c.ville LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        $totalCampus = $countBuilder->getQuery()->getSingleScalarResult();

        $nombrePages = ceil($totalCampus / $perPage);

        return $view->render($response, 'campus/liste_admin.html.twig', [
            'listeCampus'  => $campusAffiches,
            'pageActuelle' => $page,
            'nombrePages'  => $nombrePages,
            'searchTerm'   => $search
        ]);
    }

    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $parsedBody = $request->getParsedBody();
        $success = false;

        if ($request->getMethod() === 'POST') {
            // Création du campus (le constructeur attend la ville)
            $nouveauCampus = new Campus(
                trim($parsedBody['ville'] ?? '')
            );

            $this->entityManager->persist($nouveauCampus);
            $this->entityManager->flush();

            $success = true;
        }

        return $view->render($response, 'campus/ajout.html.twig', [
            'success' => $success
        ]);
    }

    public function modifier(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $id = (int) $args['id'];
        $campus = $this->entityManager->find(Campus::class, $id);

        if (!$campus) {
            return $response->withStatus(404);
        }

        $success = false;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            // On met à jour la ville
            $campus->setVille(trim($data['ville'] ?? ''));

            $this->entityManager->flush();
            $success = true;

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('campus-admin'); // À vérifier avec le nom de ta route

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        return $view->render($response, 'campus/modifier.html.twig', [
            'campus'  => $campus,
            'success' => $success,
        ]);
    }

    public function supprimer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $campus = $this->entityManager->find(Campus::class, $id);

        if ($campus) {
            $this->entityManager->remove($campus);
            $this->entityManager->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('campus-admin'); // À vérifier avec le nom de ta route

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }
}
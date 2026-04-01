<?php

namespace App\Application\Controller;

use App\Application\Domain\Entreprise;
use App\Application\Domain\Campus;
use App\Application\Domain\Offre;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

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
        $queryParams = $request->getQueryParams();
        $search = $queryParams['q'] ?? null;

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(Entreprise::class);
        $queryBuilder = $repository->createQueryBuilder('e');

        if ($search) {
            $queryBuilder->where('e.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->orderBy('e.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $entreprisesAffichees = $queryBuilder->getQuery()->getResult();

        $countBuilder = $repository->createQueryBuilder('e')->select('count(e.id)');
        if ($search) {
            $countBuilder->where('e.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        $totalEntreprises = $countBuilder->getQuery()->getSingleScalarResult();
        $nombrePages = ceil($totalEntreprises / $perPage);

        return $view->render($response, 'entreprise/liste.html.twig', [
            'entreprises' => $entreprisesAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages,
            'searchTerm' => $search
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

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('entreprises-admin');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        return $view->render($response, 'entreprise/ajout.html.twig', [
            'success' => $success
        ]);
    }

    public function listean(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $queryParams = $request->getQueryParams();
        $search = $queryParams['q'] ?? null;

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(Entreprise::class);
        $queryBuilder = $repository->createQueryBuilder('e');

        if ($search) {
            $queryBuilder->where('e.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->orderBy('e.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $entreprisesAffichees = $queryBuilder->getQuery()->getResult();

        $countBuilder = $repository->createQueryBuilder('e')->select('count(e.id)');
        if ($search) {
            $countBuilder->where('e.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        $totalEntreprises = $countBuilder->getQuery()->getSingleScalarResult();
        $nombrePages = ceil($totalEntreprises / $perPage);

        return $view->render($response, 'entreprise/liste_anonyme.html.twig', [
            'entreprises' => $entreprisesAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages,
            'searchTerm' => $search
        ]);
    }

    public function listeAdmin(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $queryParams = $request->getQueryParams();
        $search = $queryParams['q'] ?? null;  

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(Entreprise::class);
        $queryBuilder = $repository->createQueryBuilder('e');

        if ($search) {
            $queryBuilder->where('e.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->orderBy('e.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $entreprisesAffichees = $queryBuilder->getQuery()->getResult();

        $countBuilder = $repository->createQueryBuilder('e')
            ->select('count(e.id)');
        if ($search) {
            $countBuilder->where('e.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        $totalEntreprises = $countBuilder->getQuery()->getSingleScalarResult();

        $nombrePages = ceil($totalEntreprises / $perPage);

        return $view->render($response, 'entreprise/liste_admin.html.twig', [
            'entreprises' => $entreprisesAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages,
            'searchTerm' => $search 
        ]);
    }
    public function modifier(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $id = (int) $args['id'];
        $Entreprise = $this->entityManager->find(Entreprise::class, $id);

        if (!$Entreprise) {
            return $response->withStatus(404);
        }

        $success = false;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $Entreprise->setNom(trim($data['nom'] ?? ''));
            $Entreprise->setAdresse(trim($data['adresse'] ?? ''));
            $Entreprise->setSiret(trim($data['siret'] ?? ''));
            $Entreprise->setDomaine(trim($data['domaine'] ?? ''));
            $Entreprise->setTaille(trim($data['taille'] ?? ''));

            $this->entityManager->flush();
            $success = true;

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('entreprises-admin');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        $campuses = $this->entityManager->getRepository(Campus::class)->findAll();

        return $view->render($response, 'entreprise/modifier.html.twig', [
            'Entreprise' => $Entreprise,
            'success' => $success,
            'campuses' => $campuses
        ]);
    }
    public function supprimer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $Entreprise = $this->entityManager->find(Entreprise::class, $id);

        if ($Entreprise) {
            $this->entityManager->remove($Entreprise);
            $this->entityManager->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('entreprises-admin');

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }

    public function showOffres(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $id = (int)$args['id'];

        $entreprise = $this->entityManager->find(Entreprise::class, $id);

        if (!$entreprise) {
            return $response->withStatus(404);
        }

        $offres = $this->entityManager->getRepository(Offre::class)->findBy(['entreprise' => $entreprise]);

        return $view->render($response, 'entreprise/offres.html.twig', [
            'entreprise' => $entreprise,
            'offres' => $offres,
        ]);
    }

    public function noter(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int)$args['id'];

        $data = $request->getParsedBody();
        $noteSaisie = isset($data['note']) ? (float)$data['note'] : null;

        if ($noteSaisie !== null && $noteSaisie >= 1 && $noteSaisie <= 5) {

            $entreprise = $this->entityManager->getRepository(Entreprise::class)->find($id);

            if ($entreprise) {
                
                $noteActuelle = $entreprise->getNote();

                if (!$noteActuelle || $noteActuelle == 0) {
                    $nouvelleMoyenne = $noteSaisie;
                } else {
                    $nouvelleMoyenne = ($noteActuelle + $noteSaisie) / 2;
                }

                $entreprise->setNote(round($nouvelleMoyenne, 1));

                $this->entityManager->flush();
            }
        }


        return $response
            ->withHeader('Location', '/entreprise')
            ->withStatus(302);
    }
}

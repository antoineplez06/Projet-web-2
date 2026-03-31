<?php

namespace App\Application\Controller;

use App\Application\Domain\Offre;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;
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

        // Récupération des offres avec pagination
        $offresAffichees = $repository->findBy(
            [],
            ['idOffre' => 'DESC'], // Tri par ID décroissant
            $perPage,
            $offset
        );

        $totalOffres = $repository->count([]);
        $nombrePages = ceil($totalOffres / $perPage);

        return $view->render($response, 'offre/liste.html.twig', [
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

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('offres-admin');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        return $view->render($response, 'offre/ajout.html.twig', [
            'success' => $success
        ]);
    }

    public function afficherFormulairePostuler(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $idOffre = $args['id'];

        return $view->render($response, 'offre/postuler.html.twig', [
            'id' => $idOffre
        ]);
    }

    public function listean(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        $params = $request->getQueryParams();

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(Offre::class);

        // Récupération des offres avec pagination
        $offresAffichees = $repository->findBy(
            [],
            ['idOffre' => 'DESC'], // Tri par ID décroissant
            $perPage,
            $offset
        );

        $totalOffres = $repository->count([]);
        $nombrePages = ceil($totalOffres / $perPage);

        return $view->render($response, 'offre/liste_anonyme.html.twig', [
            'offres' => $offresAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages,
            'filtres'        => $params
        ]);
    }

    public function listeAdmin(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $queryParams = $request->getQueryParams();

        // Récupération des filtres depuis l'URL (search, lieu)
        $search = $queryParams['search'] ?? null;
        $lieu = $queryParams['lieu'] ?? null;

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(Offre::class);
        $queryBuilder = $repository->createQueryBuilder('o');

        // 1. Filtre par recherche texte (nom ou entreprise)
        if ($search) {
            $queryBuilder->andWhere('o.nom LIKE :search OR o.entreprise LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // 2. Filtre par lieu (depuis la modale)
        if ($lieu) {
            $queryBuilder->andWhere('o.lieu = :lieu')
                ->setParameter('lieu', $lieu);
        }

        // Tri et Pagination
        $queryBuilder->orderBy('o.idOffre', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $offresAffichees = $queryBuilder->getQuery()->getResult();

        // Calcul du total pour la pagination (en tenant compte des mêmes filtres)
        $countBuilder = $repository->createQueryBuilder('o')
            ->select('count(o.idOffre)');

        if ($search) {
            $countBuilder->andWhere('o.nom LIKE :search OR o.entreprise LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($lieu) {
            $countBuilder->andWhere('o.lieu = :lieu')
                ->setParameter('lieu', $lieu);
        }

        $totalOffres = $countBuilder->getQuery()->getSingleScalarResult();

        $nombrePages = ceil($totalOffres / $perPage);

        return $view->render($response, 'offre/liste_admin.html.twig', [
            'offres'       => $offresAffichees,
            'pageActuelle' => $page,
            'nombrePages'  => $nombrePages,
            'filtres'      => $queryParams // On passe tout le tableau pour pré-remplir la vue
        ]);
    }

    public function modifier(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $id = (int) $args['id'];
        $offre = $this->entityManager->find(Offre::class, $id);

        if (!$offre) {
            return $response->withStatus(404);
        }

        $success = false;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $offre->setNom(trim($data['nom'] ?? ''));
            $offre->setDuree(trim($data['duree'] ?? ''));
            $offre->setExigenceEtude(trim($data['exigenceEtude'] ?? $data['exigence_etude'] ?? ''));
            $offre->setEntreprise(trim($data['entreprise'] ?? ''));
            
            if (!empty($data['date'])) {
                $offre->setDate(new DateTimeImmutable($data['date']));
            }
            
            $offre->setRemuneration((float) ($data['remuneration'] ?? 0));
            $offre->setDescription(trim($data['description'] ?? ''));
            $offre->setPresentielOuDistanciel(trim($data['presentielOuDistanciel'] ?? $data['presentiel_ou_distanciel'] ?? ''));

            $this->entityManager->flush();
            $success = true;

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('offres-admin');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        return $view->render($response, 'offre/modifier.html.twig', [
            'offre' => $offre,
            'success' => $success,
        ]);
    }

    public function supprimer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $offre = $this->entityManager->find(Offre::class, $id);

        if ($offre) {
            $this->entityManager->remove($offre);
            $this->entityManager->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('offres-admin');

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }
}

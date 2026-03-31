<?php

namespace App\Application\Controller;

use App\Application\Domain\Offre;
use App\Application\Domain\Campus; // N'oublie pas cet import !
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
        $queryBuilder = $repository->createQueryBuilder('o');


        $user = $_SESSION['user'] ?? null;

        if ($user && $user->getRoleValue() === 'etudiant') {
            $campusEtudiant = $user->getCampus();

            if ($campusEtudiant) {
                $queryBuilder->andWhere('o.campus = :campus')
                    ->setParameter('campus', $campusEtudiant);
            }
        }

        $queryBuilder->orderBy('o.idOffre', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $offresAffichees = $queryBuilder->getQuery()->getResult();

        $countBuilder = $repository->createQueryBuilder('o')
            ->select('count(o.idOffre)');

        if ($user && $user->getRoleValue() === 'etudiant' && $user->getCampus()) {
            $countBuilder->andWhere('o.campus = :campus')
                ->setParameter('campus', $user->getCampus());
        }

        $totalOffres = $countBuilder->getQuery()->getSingleScalarResult();
        $nombrePages = ceil($totalOffres / $perPage);

        return $view->render($response, 'offre/liste.html.twig', [
            'offres'       => $offresAffichees,
            'pageActuelle' => $page,
            'nombrePages'  => $nombrePages,
            'filtres'      => $params
        ]);
    }

    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $parsedBody = $request->getParsedBody();
        $success = false;

        if ($request->getMethod() === 'POST') {
            $idEntreprise = $parsedBody['entreprise'] ?? null;

            $entrepriseSelectionnee = null;
            if ($idEntreprise) {
                $entrepriseSelectionnee = $this->entityManager->find(\App\Application\Domain\Entreprise::class, (int) $idEntreprise);
            }

            if (!$entrepriseSelectionnee) {
                throw new \Exception("Veuillez sélectionner une entreprise valide.");
            }

            $nouvelleOffre = new Offre(
                $parsedBody['nom'] ?? '',
                $parsedBody['duree'] ?? '',
                $parsedBody['exigence_etude'] ?? '',
                $entrepriseSelectionnee, // <--- C'EST ICI LA CORRECTION
                new DateTimeImmutable($parsedBody['date']),
                (float) ($parsedBody['remuneration'] ?? 0),
                $parsedBody['description'] ?? '',
                $parsedBody['presentiel_ou_distanciel'] ?? ''
            );

            $idCampus = $parsedBody['id_campus'] ?? null;
            if ($idCampus) {
                $campusSelectionne = $this->entityManager->find(Campus::class, (int) $idCampus);
                if ($campusSelectionne) {
                    $nouvelleOffre->setCampus($campusSelectionne);
                }
            }

            $this->entityManager->persist($nouvelleOffre);
            $this->entityManager->flush();

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('offres-admin');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        $campuses = $this->entityManager->getRepository(Campus::class)->findAll();
        $entreprises = $this->entityManager->getRepository(\App\Application\Domain\Entreprise::class)->findAll();

        return $view->render($response, 'offre/ajout.html.twig', [
            'success' => $success,
            'campuses' => $campuses,
            'entreprises' => $entreprises 
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
        $search = $queryParams['q'] ?? null; // Récupère le terme de recherche

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(Offre::class);
        $queryBuilder = $repository->createQueryBuilder('o');

        // Si une recherche est effectuée
        if ($search) {
            $queryBuilder->where('o.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->orderBy('o.idOffre', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $offresAffichees = $queryBuilder->getQuery()->getResult();

        // Calcul du total pour la pagination (en tenant compte du filtre)
        $countBuilder = $repository->createQueryBuilder('o')
            ->select('count(o.idOffre)');

        if ($search) {
            $countBuilder->where('o.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        $totalOffres = $countBuilder->getQuery()->getSingleScalarResult();

        $nombrePages = ceil($totalOffres / $perPage);

        return $view->render($response, 'offre/liste_admin.html.twig', [
            'offres'       => $offresAffichees,
            'pageActuelle' => $page,
            'nombrePages'  => $nombrePages,
            'searchTerm'   => $search // On renvoie le terme pour l'afficher dans l'input
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

            // --- GESTION DU CAMPUS (MODIFICATION) ---
            $idCampus = $data['id_campus'] ?? null;
            if ($idCampus) {
                $campusSelectionne = $this->entityManager->find(Campus::class, (int) $idCampus);
                if ($campusSelectionne) {
                    $offre->setCampus($campusSelectionne);
                }
            }

            $this->entityManager->flush();
            $success = true;

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('offres-admin');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        // Récupération des campus pour le formulaire de modification
        $campuses = $this->entityManager->getRepository(Campus::class)->findAll();

        return $view->render($response, 'offre/modifier.html.twig', [
            'offre' => $offre,
            'success' => $success,
            'campuses' => $campuses // On envoie la liste des campus ici
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

<?php

namespace App\Application\Controller;

use App\Application\Domain\user; 
use App\Application\Domain\Role;

use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use App\Application\Domain\Campus;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

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
        $queryParams = $request->getQueryParams();
        $search = $queryParams['q'] ?? null;

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(user::class);
        $queryBuilder = $repository->createQueryBuilder('u');

        $queryBuilder->where('u.role = :role')
            ->setParameter('role', 'etudiant');


        $userConnecte = $request->getAttribute('user');


        if ($userConnecte && $userConnecte->getRoleValue() === 'pilote') {
            $campusPilote = $userConnecte->getCampus();

            if ($campusPilote) {
                $queryBuilder->andWhere('u.campus = :campus')
                    ->setParameter('campus', $campusPilote->getIdCampus());
            }
        }


        if ($search) {
            $queryBuilder->andWhere('u.nom LIKE :search OR u.prenom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }


        $queryBuilder->orderBy('u.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $etudiantAffichees = $queryBuilder->getQuery()->getResult();


        $countBuilder = $repository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'etudiant');

        if ($userConnecte && strtolower($userConnecte->getRoleValue()) === 'pilote') {
            $campusPilote = $userConnecte->getCampus();
            if ($campusPilote) {
                $countBuilder->andWhere('u.campus = :campus_id')
                    ->setParameter('campus_id', $campusPilote->getIdCampus());
            }
        }

        if ($search) {
            $countBuilder->andWhere('u.nom LIKE :search OR u.prenom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $totalItems = $countBuilder->getQuery()->getSingleScalarResult();
        $nombrePages = ceil($totalItems / $perPage);

        return $view->render($response, 'etudiant/liste.html.twig', [
            'etudiant'     => $etudiantAffichees,
            'pageActuelle' => $page,
            'nombrePages'  => $nombrePages,
            'searchTerm'   => $search
        ]);
    }

    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $data = $request->getParsedBody();
        $success = false;

        if ($request->getMethod() === 'POST') {
            $prenom = trim($data['prenom'] ?? '');
            $nom = trim($data['nom'] ?? '');
            $tel = trim($data['numeroTelephone'] ?? '');
            $genre = trim($data['genre'] ?? '');
            $email = trim($data['email'] ?? '');
            $mdp = password_hash(trim($data['motDePasse'] ?? ''), PASSWORD_BCRYPT);
            $promo = trim($data['promo'] ?? '');
            $role = Role::ETUDIANT;

            $dateNaissanceRaw = $data['dateNaissance'] ?? 'now';
            try {
                $dateNaissance = new \DateTimeImmutable($dateNaissanceRaw);
            } catch (\Exception $e) {
                $dateNaissance = new \DateTimeImmutable();
            }

            $idCampus = $data['id_campus'] ?? null;
            $campusSelectionne = null;
            if ($idCampus) {
                $campusSelectionne = $this->entityManager->find(Campus::class, (int)$idCampus);
            }

            $nouvelEtudiant = new user(
                $prenom,
                $nom,
                $tel,
                $genre,
                $email,
                $mdp,
                $dateNaissance,
                $promo,
                $role,
                $campusSelectionne 
            );


            $this->entityManager->persist($nouvelEtudiant);
            $this->entityManager->flush();

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('liste-etudiants');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        $campuses = $this->entityManager->getRepository(Campus::class)->findAll();

        return $view->render($response, 'etudiant/ajout.html.twig', [
            "success" => $success,
            "campuses" => $campuses 
        ]);
    }

    public function modifier(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $id = (int) $args['id'];
        $Etudiant = $this->entityManager->find(user::class, $id);

        if (!$Etudiant) {
            return $response->withStatus(404);
        }

        $success = false;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $Etudiant->setPrenom(trim($data['prenom'] ?? ''));
            $Etudiant->setNom(trim($data['nom'] ?? ''));
            $Etudiant->setNumeroTelephone(trim($data['numeroTelephone'] ?? ''));
            $Etudiant->setGenre(trim($data['genre'] ?? ''));
            $Etudiant->setEmail(trim($data['email'] ?? ''));
            $Etudiant->setPromo(trim($data['promo'] ?? ''));

            if (!empty($data['motDePasse'])) {
                $Etudiant->setMotDePasse(password_hash($data['motDePasse'], PASSWORD_BCRYPT)); 
            }

            if (!empty($data['dateNaissance'])) {
                $date = new \DateTimeImmutable($data['dateNaissance']);
                $Etudiant->setDateNaissance($date);
            }

            $idCampus = $data['id_campus'] ?? null;
            if ($idCampus) {
                $campusSelectionne = $this->entityManager->find(Campus::class, (int)$idCampus);
                $Etudiant->setCampus($campusSelectionne);
            }

            $this->entityManager->flush();
            $success = true;

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('liste-etudiants');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }


        $campuses = $this->entityManager->getRepository(Campus::class)->findAll();

        return $view->render($response, 'etudiant/modifier.html.twig', [
            'etudiant' => $Etudiant,
            'success' => $success,
            'campuses' => $campuses 
        ]);
    }

    public function supprimer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $Etudiant = $this->entityManager->find(user::class, $id);

        if ($Etudiant) {
            $this->entityManager->remove($Etudiant);
            $this->entityManager->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('liste-etudiants');

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }
}

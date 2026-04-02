<?php

namespace App\Application\Controller;

use App\Application\Domain\User;
use App\Application\Domain\Role;

use Doctrine\ORM\EntityManager;
use App\Application\Domain\Campus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class PiloteController
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
            ->setParameter('role', 'pilote');

        if ($search) {
            $queryBuilder->andWhere('u.nom LIKE :search OR u.prenom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->orderBy('u.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $piloteAffiches = $queryBuilder->getQuery()->getResult();

        $countBuilder = $repository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'pilote');

        if ($search) {
            $countBuilder->andWhere('u.nom LIKE :search OR u.prenom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $totalItems = $countBuilder->getQuery()->getSingleScalarResult();
        $nombrePages = ceil($totalItems / $perPage);

        return $view->render($response, 'pilote/liste.html.twig', [
            'pilote'       => $piloteAffiches,
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
            $role = Role::PILOTE;

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


            $nouveauPilote = new user(
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

            $this->entityManager->persist($nouveauPilote);
            $this->entityManager->flush();

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('liste-pilotes');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }
        
        $campuses = $this->entityManager->getRepository(Campus::class)->findAll();


        return $view->render($response, 'pilote/ajout.html.twig', [
            "success" => $success,
            "campuses" => $campuses,
        ]);
    }


    public function modifier(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $id = (int) $args['id'];
        $Pilote = $this->entityManager->find(user::class, $id);

        if (!$Pilote) {
            return $response->withStatus(404);
        }

        $success = false;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $Pilote->setPrenom(trim($data['prenom'] ?? ''));
            $Pilote->setNom(trim($data['nom'] ?? ''));
            $Pilote->setNumeroTelephone(trim($data['numeroTelephone'] ?? ''));
            $Pilote->setGenre(trim($data['genre'] ?? ''));
            $Pilote->setEmail(trim($data['email'] ?? ''));
            $Pilote->setPromo(trim($data['promo'] ?? ''));

            if (!empty($data['motDePasse'])) {
                $Pilote->setMotDePasse($data['motDePasse']);
            }

            if (!empty($data['dateNaissance'])) {
                $date = new \DateTimeImmutable($data['dateNaissance']);
                $Pilote->setDateNaissance($date);
            }

            $this->entityManager->flush();
            $success = true;

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('liste-pilotes');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        return $view->render($response, 'pilote/modifier.html.twig', [
            'pilote' => $Pilote,
            'success' => $success,

        ]);
    }
    public function supprimer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $Pilote = $this->entityManager->find(user::class, $id);

        if ($Pilote) {
            $this->entityManager->remove($Pilote);
            $this->entityManager->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('liste-pilotes');

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }
}

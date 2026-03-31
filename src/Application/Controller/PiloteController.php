<?php

namespace App\Application\Controller;

use App\Application\Domain\User;
use App\Application\Domain\Role;

use Doctrine\ORM\EntityManager;
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
        $search = $queryParams['q'] ?? null; // Récupère le terme de recherche

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $repository = $this->entityManager->getRepository(user::class);
        $queryBuilder = $repository->createQueryBuilder('u');

        // 1. Condition obligatoire : on ne veut QUE les pilotes
        $queryBuilder->where('u.role = :role')
            ->setParameter('role', 'pilote');

        // 2. Si une recherche est effectuée (sur nom ou prénom)
        if ($search) {
            $queryBuilder->andWhere('u.nom LIKE :search OR u.prenom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Tri et Pagination
        $queryBuilder->orderBy('u.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $piloteAffiches = $queryBuilder->getQuery()->getResult();

        // 3. Calcul du total pour la pagination
        $countBuilder = $repository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'pilote');

        // On applique le même filtre de recherche pour le compte des pages
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
            'searchTerm'   => $search // On renvoie le terme pour l'afficher dans l'input
        ]);
    }

    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $data = $request->getParsedBody();
        $success = false;

        if ($request->getMethod() === 'POST') {
            // 1. Préparation des données (on s'assure qu'elles ne sont pas nulles)
            $prenom = trim($data['prenom'] ?? '');
            $nom = trim($data['nom'] ?? '');
            $tel = trim($data['numeroTelephone'] ?? '');
            $genre = trim($data['genre'] ?? '');
            $email = trim($data['email'] ?? '');
            $mdp = password_hash(trim($data['motDePasse'] ?? ''), PASSWORD_BCRYPT); // Toujours hasher les MDP !
            $promo = trim($data['promo'] ?? '');
            $role = Role::PILOTE;

            // Gestion de la date de naissance (DateTimeImmutable requis)
            $dateNaissanceRaw = $data['dateNaissance'] ?? 'now';
            try {
                $dateNaissance = new \DateTimeImmutable($dateNaissanceRaw);
            } catch (\Exception $e) {
                $dateNaissance = new \DateTimeImmutable(); // Date par défaut si erreur
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
                $role
            );

            $this->entityManager->persist($nouveauPilote);
            $this->entityManager->flush();

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('liste-pilotes');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        return $view->render($response, 'pilote/ajout.html.twig', [
            "success" => $success
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

            // On remplit l'objet avec les nouvelles valeurs
            $Pilote->setPrenom(trim($data['prenom'] ?? ''));
            $Pilote->setNom(trim($data['nom'] ?? ''));
            $Pilote->setNumeroTelephone(trim($data['numeroTelephone'] ?? ''));
            $Pilote->setGenre(trim($data['genre'] ?? ''));
            $Pilote->setEmail(trim($data['email'] ?? ''));
            $Pilote->setPromo(trim($data['promo'] ?? ''));

            // On ajoute la gestion du mot de passe
            if (!empty($data['motDePasse'])) {
                $Pilote->setMotDePasse($data['motDePasse']);
            }

            // On ajoute la gestion de la date
            if (!empty($data['dateNaissance'])) {
                $date = new \DateTimeImmutable($data['dateNaissance']);
                $Pilote->setDateNaissance($date);
            }

            // On sauvegarde tout en base de données
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

<?php

namespace App\Application\Controller;
use App\Application\Domain\User;

use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
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


        $Etudiant = $this->entityManager
            ->getRepository(user::class)
            ->findBy(['role' => 'etudiant']);

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        if ($page < 1)
            $page = 1;

        $perPage = 5;
        $totalItems = count($Etudiant);
        $nombrePages = ceil($totalItems / $perPage);

        $offset = ($page - 1) * $perPage;
        $etudiantAffichees = array_slice($Etudiant, $offset, $perPage);

        return $view->render($response, 'liste-etudiant.html.twig', [
            'etudiant' => $etudiantAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages
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
            $role = 'etudiant';

            // Gestion de la date de naissance (DateTimeImmutable requis)
            $dateNaissanceRaw = $data['dateNaissance'] ?? 'now';
            try {
                $dateNaissance = new \DateTimeImmutable($dateNaissanceRaw);
            } catch (\Exception $e) {
                $dateNaissance = new \DateTimeImmutable(); // Date par défaut si erreur
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
                $role
            );

            $this->entityManager->persist($nouvelEtudiant);
            $this->entityManager->flush();
            
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('liste-etudiant');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);

        }

        return $view->render($response, 'ajout-etudiant.html.twig', [
            "success" => $success
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

            // On remplit l'objet avec les nouvelles valeurs
            $Etudiant->setPrenom(trim($data['prenom'] ?? ''));
            $Etudiant->setNom(trim($data['nom'] ?? ''));
            $Etudiant->setNumeroTelephone(trim($data['numeroTelephone'] ?? ''));
            $Etudiant->setGenre(trim($data['genre'] ?? ''));
            $Etudiant->setEmail(trim($data['email'] ?? ''));
            $Etudiant->setPromo(trim($data['promo'] ?? ''));

            // On ajoute la gestion du mot de passe
            if (!empty($data['motDePasse'])) {
                $Etudiant->setMotDePasse($data['motDePasse']);
            }

            // On ajoute la gestion de la date
            if (!empty($data['dateNaissance'])) {
                $date = new \DateTimeImmutable($data['dateNaissance']);
                $Etudiant->setDateNaissance($date);
            }

            // On sauvegarde tout en base de données
            $this->entityManager->flush();
            $success = true;

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('liste-etudiant');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        return $view->render($response, 'modifier-etudiant.html.twig', [
            'etudiant' => $Etudiant,
            'success' => $success,

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
        $url = $routeParser->urlFor('liste-etudiant');

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }
}



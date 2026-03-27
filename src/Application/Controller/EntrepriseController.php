<?php

namespace App\Application\Controller;
use App\Application\Domain\Entreprise;

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
    $page = isset($args['page']) ? (int) $args['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    
    $repository = $this->entityManager->getRepository(Entreprise::class);


    $entreprisesAffichees = $repository->findBy(
        [],                         
        ['id' => 'DESC'],    
        $perPage,                   
        $offset                     
    );


    $totalEntreprises = $repository->count([]);
    $nombrePages = ceil($totalEntreprises / $perPage);

    return $view->render($response, 'liste-entreprises.html.twig', [
        'entreprises' => $entreprisesAffichees,
        'pageActuelle' => $page,
        'nombrePages' => $nombrePages
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

        $success = true;
    }

    return $view->render($response, 'ajout-entreprise.html.twig', [
        'success' => $success
    ]);
    }

    public function listean(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
    $page = isset($args['page']) ? (int) $args['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    
    $repository = $this->entityManager->getRepository(Entreprise::class);


    $entreprisesAffichees = $repository->findBy(
        [],                         
        ['id' => 'DESC'],    
        $perPage,                   
        $offset                     
    );


    $totalEntreprises = $repository->count([]);
    $nombrePages = ceil($totalEntreprises / $perPage);

    return $view->render($response, 'Entreprises-an.html.twig', [
        'entreprises' => $entreprisesAffichees,
        'pageActuelle' => $page,
        'nombrePages' => $nombrePages
    ]);
    }

        public function listeAdmin(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
    $page = isset($args['page']) ? (int) $args['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    
    $repository = $this->entityManager->getRepository(Entreprise::class);


    $entreprisesAffichees = $repository->findBy(
        [],                         
        ['id' => 'DESC'],    
        $perPage,                   
        $offset                     
    );


    $totalEntreprises = $repository->count([]);
    $nombrePages = ceil($totalEntreprises / $perPage);

    return $view->render($response, 'liste-entreprises-admin.html.twig', [
        'entreprises' => $entreprisesAffichees,
        'pageActuelle' => $page,
        'nombrePages' => $nombrePages
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

            // On remplit l'objet avec les nouvelles valeurs
            $Entreprise->setNom(trim($data['nom'] ?? ''));
            $Entreprise->setNumeroTelephone(trim($data['numeroTelephone'] ?? ''));
            $Entreprise->setGenre(trim($data['genre'] ?? ''));
            $Entreprise->setEmail(trim($data['email'] ?? ''));
            $Entreprise->setPromo(trim($data['promo'] ?? ''));

            // On ajoute la gestion du mot de passe
            if (!empty($data['motDePasse'])) {
                $Entreprise->setMotDePasse($data['motDePasse']);
            }

            // On ajoute la gestion de la date
            if (!empty($data['dateNaissance'])) {
                $date = new \DateTimeImmutable($data['dateNaissance']);
                $Entreprise->setDateNaissance($date);
            }

            // On sauvegarde tout en base de données
            $this->entityManager->flush();
            $success = true;

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('entreprises-admin');

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        return $view->render($response, 'modifier-Entreprise.html.twig', [
            'Entreprise' => $Entreprise,
            'success' => $success,

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
}

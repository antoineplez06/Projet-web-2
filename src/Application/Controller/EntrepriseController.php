<?php

namespace App\Application\Controller;
use App\Application\Domain\Entreprise;
use App\Application\Domain\Campus; 
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

        return $view->render($response, 'entreprise/liste.html.twig', [
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

        return $view->render($response, 'entreprise/liste_anonyme.html.twig', [
            'entreprises' => $entreprisesAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages
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

        $repository = $this->entityManager->getRepository(Entreprise::class);
        $queryBuilder = $repository->createQueryBuilder('e');

        // Si une recherche est effectuée
        if ($search) {
            $queryBuilder->where('e.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->orderBy('e.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $entreprisesAffichees = $queryBuilder->getQuery()->getResult();

        // Calcul du total pour la pagination (en tenant compte du filtre)
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
            'searchTerm' => $search // On renvoie le terme pour l'afficher dans l'input
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
            $Entreprise->setAdresse(trim($data['adresse'] ?? ''));
            $Entreprise->setSiret(trim($data['siret'] ?? ''));
            $Entreprise->setDomaine(trim($data['domaine'] ?? ''));
            $Entreprise->setTaille(trim($data['taille'] ?? ''));

            // On sauvegarde tout en base de données
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

        // Exemple dans ton EntrepriseController
    public function showOffres(int $id, OffreRepository $offreRepository, EntrepriseRepository $entrepriseRepo)
    {
        $entreprise = $entrepriseRepo->find($id);
        // On récupère uniquement les offres liées à cette entreprise
        $offres = $offreRepository->findBy(['entreprise' => $entreprise]);

        return $this->render('entreprise/offres.html.twig', [
            'entreprise' => $entreprise,
            'offres' => $offres,
        ]);
    }

    public function noter(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    // 1. Récupérer l'id de l'entreprise
    $id = (int)$args['id'];
    
    // 2. Récupérer la note du formulaire (POST)
    $data = $request->getParsedBody();
    $noteSaisie = isset($data['note']) ? (float)$data['note'] : null;

    // Vérification de sécurité (entre 1 et 5)
    if ($noteSaisie !== null && $noteSaisie >= 1 && $noteSaisie <= 5) {
        
        // 3. Trouver l'entreprise via Doctrine
        $entreprise = $this->entityManager->getRepository(Entreprise::class)->find($id);

        if ($entreprise) {
            // Logique de calcul :
            // Si l'entreprise n'a pas encore de note (null ou 0), on met la note directement.
            // Sinon, on fait une moyenne entre l'ancienne et la nouvelle.
            $noteActuelle = $entreprise->getNote();

            if (!$noteActuelle || $noteActuelle == 0) {
                $nouvelleMoyenne = $noteSaisie;
            } else {
                $nouvelleMoyenne = ($noteActuelle + $noteSaisie) / 2;
            }

            // On enregistre avec un chiffre après la virgule (ex: 4.5)
            $entreprise->setNote(round($nouvelleMoyenne, 1));
            
            $this->entityManager->flush();
        }
    }

    // 4. Redirection vers la page précédente (la liste)
    // On utilise l'en-tête Location pour rafraîchir proprement
    return $response
        ->withHeader('Location', '/entreprise') 
        ->withStatus(302);
}
}

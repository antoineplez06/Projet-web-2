<?php

namespace App\Application\Controller;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Doctrine\ORM\EntityManager;
use App\Application\Domain\User;
use App\Application\Domain\Offre;
use App\Application\Domain\Entreprise;

class HomeController
{
    private EntityManager $entityManager;

    // Le constructeur reçoit l'EntityManager grâce au container de Slim
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function getCompteurs(): array
    {
        $totalOffres = $this->entityManager->getRepository(Offre::class)
            ->createQueryBuilder('o')
            ->select('count(o.idOffre)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalEntreprises = $this->entityManager->getRepository(Entreprise::class)
            ->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalEtudiants = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'etudiant')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalOffres'      => $totalOffres,
            'totalEntreprises' => $totalEntreprises,
            'totalEtudiants'   => $totalEtudiants,
        ];
    }

    public function home(Request $request, Response $response, array $args): Response
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'accueil/index.html.twig', $this->getCompteurs());
    }


    public function accueilCo(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Puisque UserTwigMiddleware ne voit pas la session, on récupère l'user manuellement
        $userId = $_SESSION['user_id'] ?? null;
        $user = null;
        if ($userId) {
            $user = $this->entityManager->getRepository(User::class)->find($userId);
        }

        $view = Twig::fromRequest($request);
        return $view->render($response, 'accueil/index.html.twig', array_merge([
            'user'       => $user,
            'user_roles' => $_SESSION['user_roles'] ?? null
        ], $this->getCompteurs()
        ));
    }

    public function deconnexion(Request $request, Response $response): Response
    {
        // Nettoyage complet de la session
        $_SESSION = [];
        session_destroy();

        // Redirection vers l'accueil public
        return $response->withHeader('Location', '/')->withStatus(302);
    }

     public function footer(Request $request, Response $response, array $args): Response
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'footer/mentionslégales.html.twig', $this->getCompteurs());
    }
}

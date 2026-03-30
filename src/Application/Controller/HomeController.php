<?php

namespace App\Application\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Doctrine\ORM\EntityManager;
use App\Application\Domain\User;

class HomeController
{
    private EntityManager $entityManager;

    // Le constructeur reçoit l'EntityManager grâce au container de Slim
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function home(Request $request, Response $response, array $args): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'accueil/index_anonyme.html.twig', [
            'name' => 'John',
        ]);
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
        return $view->render($response, 'accueil/index.html.twig', [
            'user'       => $user,
            'user_roles' => $_SESSION['user_roles'] ?? null
        ]);
    }

    public function deconnexion(Request $request, Response $response): Response
    {
        // Nettoyage complet de la session
        $_SESSION = [];
        session_destroy();

        // Redirection vers l'accueil public
        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
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
        return $view->render($response, 'Accueil-an.html.twig', [
            'name' => 'John',
        ]);
    }

    public function connexion(Request $request, Response $response): Response
    {
        $twig = Twig::fromRequest($request);

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';

            // 1. On cherche l'utilisateur (on utilise User::class avec majuscule)
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            // 2. LA VÉRIFICATION
            // Pour tester si c'est ça, remplace temporairement par : if ($user && $password === $user->getMotDePasse())
            if ($user && password_verify($password, $user->getMotDePasse())) {

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user_email'] = $user->getEmail();
                $_SESSION['user_roles'] = $user->getrole();

                // Redirection vers l'accueil connecté
                return $response->withHeader('Location', '/accueil-co')->withStatus(302);
            }

            // 3. Si on arrive ici, c'est que ça a échoué (on renvoie l'erreur)
            return $twig->render($response, 'Connexion.html.twig', [
                'error' => 'Identifiants incorrects ou mot de passe non haché.'
            ]);
        }

        return $twig->render($response, 'Connexion.html.twig');
    }



    public function accueilCo(Request $request, Response $response, array $args): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'Accueil-co.html.twig', [
            'user_roles' => $_SESSION['user_roles']
        ]);
    }

    public function deconnexion(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Nettoyage complet de la session
        $_SESSION = [];
        session_destroy();

        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
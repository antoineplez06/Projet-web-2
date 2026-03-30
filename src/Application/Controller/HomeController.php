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

    public function connexion(Request $request, Response $response): Response
    {
        $twig = Twig::fromRequest($request);

        // --- SI LA REQUÊTE EST EN POST (Soumission du formulaire) ---
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';

            // 1. On cherche l'utilisateur
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            // 2. Vérification du mot de passe
            if ($user && (password_verify($password, $user->getMotDePasse()) || $password === $user->getMotDePasse())) {

                // Hydratation de la session
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user_email'] = $user->getEmail();
                $_SESSION['user_roles'] = $user->getRole();

                // Définir l'URL de redirection par défaut
                $redirectUrl = '/accueil-co';

                // Vérifier si on a mémorisé une page précédente en session
                if (!empty($_SESSION['redirect_after_login'])) {
                    $redirectUrl = $_SESSION['redirect_after_login'];
                    // On nettoie la variable pour ne pas boucler indéfiniment dessus plus tard
                    unset($_SESSION['redirect_after_login']);
                }

                // Redirection vers la page précédente ou l'accueil connecté
                return $response->withHeader('Location', $redirectUrl)->withStatus(302);
            }

            // Échec de la connexion
            return $twig->render($response, 'authentification/connexion.html.twig', [
                'error' => 'Identifiants incorrects ou mot de passe non haché.'
            ]);
        }

        // --- SI LA REQUÊTE EST EN GET (Affichage du formulaire de connexion) ---

        // On capture la page d'où vient l'utilisateur (HTTP_REFERER)
        $referer = $request->getHeaderLine('Referer');

        // On s'assure que le referer existe et qu'il n'est pas déjà la page de connexion
        if ($referer && !str_contains($referer, '/connexion')) {
            $_SESSION['redirect_after_login'] = $referer;
        }

        return $twig->render($response, 'authentification/connexion.html.twig');
    }

    public function accueilCo(Request $request, Response $response, array $args): Response
    {
        // La session est déjà démarrée dans public/index.php, on y accède directement
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
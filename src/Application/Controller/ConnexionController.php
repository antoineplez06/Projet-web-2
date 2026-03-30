<?php
namespace App\Application\Controller;

use App\Application\Domain\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class ConnexionController
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function connexion(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // 1. On démarre la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Si l'utilisateur est déjà connecté, on le redirige directement vers l'accueil connecté
        if (isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/accueil')->withStatus(302);
        }

        // 3. On prépare les données pour Twig. 
        // Ici on sait que est_connecte est false, sinon la redirection au-dessus aurait eu lieu.
        $model = [
            'est_connecte' => false
        ];
        
        // 4. Traitement du formulaire de connexion
        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $email = $parsedBody['email'] ?? '';
            $password = $parsedBody['password'] ?? '';

            $userRepo  = $this->em->getRepository(User::class);
            $user = $userRepo->findOneBy(['email' => $email]);

            if ($user === null) {   
                $model['error'] = "Email introuvable";
            } else if (password_verify($password, $user->getMotDePasse())) {
                // Authentification réussie
                $_SESSION['user_id'] = $user->getId();
                
                // Redirection vers l'accueil connecté
                return $response->withHeader('Location', '/accueil')->withStatus(302);
            } else {
                $model['error'] = "Mot de passe incorrect";
            }
        }
        
        // 5. Affichage de la vue
        $view = Twig::fromRequest($request);
        return $view->render($response, 'authentification/connexion.html.twig', $model);
    }
}   
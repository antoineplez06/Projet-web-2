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

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/accueil')->withStatus(302);
        }


        $model = [
            'est_connecte' => false
        ];
        

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $email = $parsedBody['email'] ?? '';
            $password = $parsedBody['password'] ?? '';

            $userRepo  = $this->em->getRepository(User::class);
            $user = $userRepo->findOneBy(['email' => $email]);

            if ($user === null) {   
                $model['error'] = "Email introuvable";
            } else if (password_verify($password, $user->getMotDePasse())) {

                $_SESSION['user_id'] = $user->getId();
                

                return $response->withHeader('Location', '/accueil')->withStatus(302);
            } else {
                $model['error'] = "Mot de passe incorrect";
            }
        }
        

        $view = Twig::fromRequest($request);
        return $view->render($response, 'authentification/connexion.html.twig', $model);
    }
}   
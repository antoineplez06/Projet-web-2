<?php
namespace App\Application\Controller;

use App\Domain\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class LoginController
{
    private EntityManager $em;
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function login(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $model = [];
        if ($request->getMethod() === 'POST') {
            var_dump($request->getParsedBody());
            $email = $request->getParsedBody()['email'] ?? '';
            $password = $request->getParsedBody()['password'] ?? '';
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            var_dump($password_hash);
            $userRepo  = $this->em->getRepository(user::class);
            $user = $userRepo->findOneBy(['email' => $email]);
            if($user == NULL){   
                $model['error'] = "Email introuvable";
            } else if (password_verify($password, $user->getMotDePasse())) {
                // Authentification réussie
                $_SESSION['id'] = $user->getId();
                return $response->withHeader('Location', '/')->withStatus(302);
            } else {
                $model['error'] = "Mot de passe incorrect";
            }
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'authentification/connexion.html.twig', $model);
    }
}
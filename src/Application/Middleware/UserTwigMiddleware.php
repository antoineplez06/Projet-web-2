<?php
namespace App\Application\Middleware;
use App\Application\Domain\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
class UserTwigMiddleware
{
    private $twig;
    private $em;
    public function __construct($twig,EntityManager $em)
    {
        $this->twig = $twig;
        $this->em = $em;
    }
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Exemple : récupérer une variable depuis la session
        $user_id = $request->getAttribute('session')['user_id'] ?? null;
        // Récupérer l'utilisateur depuis la base de données
        $user = null;
        if ($user_id) {
            $user = $this->em->getRepository(user::class)->find($user_id);
        }
        //var_dump( $request->getAttribute('session'));
        //die();
        // Ajouter la variable globale à Twig
        $this->twig->getEnvironment()->addGlobal('user', $user);
        $request = $request->withAttribute('user', $user);
        // Continuer le traitement de la requête
        return $handler->handle($request);
    }
}
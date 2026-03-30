<?php
namespace App\Application\Middleware;
use App\Domain\Role;
use App\Domain\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
class RoleCheckMiddleware
{
    private ResponseFactoryInterface $responseFactory;
    private array $roles;
    public function __construct(ResponseFactoryInterface $responseFactory, array $roles)
    {
        $this->responseFactory = $responseFactory;
        $this->roles = $roles;
    }
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user') ?? null;
        if($user === null || !in_array($user->getRole(), $this->roles)) {
            $response = $this->responseFactory->createResponse();
            return $response->withHeader('Location', '/connexion')->withStatus(302);
        }
        return $handler->handle($request);
    }
}

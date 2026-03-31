<?php
namespace App\Application\Middleware;
use App\Application\Domain\Role;
use App\Application\Domain\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
class LoggedMiddleware
{
    private ResponseFactoryInterface $responseFactory;
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user') ?? null;
        if($user === null) {
            $response = $this->responseFactory->createResponse();
            return $response->withHeader('Location', '/connexion')->withStatus(302);
        }
        return $handler->handle($request);
    }
}
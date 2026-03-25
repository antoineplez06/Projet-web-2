<?php

namespace App\Application\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    
    public function home(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
       $view = Twig::fromRequest($request);
    
        return $view->render($response, 'Accueil-an.html.twig', [
            'name' => 'John',
        ]);
    }
    public function connexion(Request $request, Response $response)
{
    $twig = Twig::fromRequest($request);

    if ($request->getMethod() === 'POST') {
        return $response->withHeader('Location', '/accueil-co')->withStatus(302);
    }

    return $twig->render($response, 'Connexion.html.twig');
}
    public function accueilCo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Accueil-co.html.twig');
}

public function deconnexion(Request $request, Response $response): ResponseInterface
{

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


    $_SESSION = [];
    session_destroy();


    return $response->withHeader('Location', '/')->withStatus(302);
}

}   
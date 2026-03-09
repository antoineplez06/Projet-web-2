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
    
        return $view->render($response, 'index.html.twig', [
            'name' => 'John',
        ]);
    }
    public function connexion(Request $request, Response $response)
{
    $twig = \Slim\Views\Twig::fromRequest($request);

    if ($request->getMethod() === 'POST') {

        $data = $request->getParsedBody();

        $identifiant = $data['identifiant'] ?? '';
        $password = $data['password'] ?? '';

        // traitement login ici
    }

    return $twig->render($response, 'connexion.html.twig');
}
}   
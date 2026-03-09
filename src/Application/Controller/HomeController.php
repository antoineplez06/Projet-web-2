<?php

namespace App\Application\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class HomeController
{
    
    public function home(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
       $view = Twig::fromRequest($request);
    
        return $view->render($response, 'index.html.twig', [
            'name' => 'John',
        ]);
    }
}   
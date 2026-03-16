<?php

namespace App\Application\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class OffreController
{

    public function liste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);



        $page = isset($args['page']) ? (int) $args['page'] : 1;
        if ($page < 1)
            $page = 1;

        $perPage = 5; // On va dire 5 par page pour tester
        $totalItems = count($entreprises);
        $nombrePages = ceil($totalItems / $perPage);

        $offset = ($page - 1) * $perPage;
        $entreprisesAffichees = array_slice($entreprises, $offset, $perPage);

        return $view->render($response, 'liste-entreprises.html.twig', [
            'entreprises' => $entreprisesAffichees,
            'pageActuelle' => $page,
            'nombrePages' => $nombrePages
        ]);
    }


    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        $parsedBody = $request->getParsedBody();
        //var_dump($parsedBody); 

        //Vérifier l'id
        $success = false;
        if ($request->getMethod() === 'POST') {
            //Ici, on ajouterait l'entreprise à la base de données
            $success = true;
        }

        return $view->render($response, 'ajout-entreprise.html.twig', [
            "nom" => $parsedBody['nom'] ?? '',
            "success" => $success
        ]);
    }


    public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'entreprise_formulaire.twig', [

        ]);
    }
}
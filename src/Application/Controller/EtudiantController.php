<?php

namespace App\Application\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class EtudiantController
{
    
    public function liste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
       $view = Twig::fromRequest($request);
    
        $etudiant = [
            ['id' => 1, 'nom' => 'CESI', 'secteur' => 'IA', 'statut' => 'Actif'],
            ['id' => 2, 'nom' => 'GreenLeaf', 'secteur' => 'Écologie', 'statut' => 'En attente'],
            ['id' => 3, 'nom' => 'CyberShield', 'secteur' => 'Sécurité', 'statut' => 'Actif'],
            ['id' => 4, 'nom' => 'BlueHorizon', 'secteur' => 'Logistique', 'statut' => 'Inactif'],
        ];

        if(isset($args['page'])){
            $page = (int)$args['page'];
            $perPage = 2;
            $offset = ($page - 1) * $perPage;
            $etudiant = array_slice($etudiant, $offset, $perPage);
        }

        return $view->render($response, 'liste-etudiant.html.twig', [
           'etudiant' => $etudiant
        ]);
    }


    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
    
        $parsedBody = $request->getParsedBody();
        //var_dump($parsedBody); 

        //Vérifier l'id
        $success = false;
        if($request->getMethod() === 'POST'){
            //Ici, on ajouterait l'entreprise à la base de données
            $success = true;
        }

        return $view->render($response, 'ajout-etudiant.html.twig', [
            "nom" => $parsedBody['nom'] ?? '',
            "success" => $success
        ]);
    }

    
    public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
       $view = Twig::fromRequest($request);

        return $view->render($response, 'etudiant-formulaire.twig', [
        
        ]);
    }


   
}
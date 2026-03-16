<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Controller\HomeController;
use App\Application\Controller\EntrepriseController;
use App\Application\Controller\EtudiantController;
use App\Application\Controller\OffreController;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', [HomeController::class, 'home']);
    $app->get('/entreprises[/{page:\d+}]', [EntrepriseController::class, 'liste'])->setName('liste-entreprises');
    $app->get('/ajout-entreprise', [EntrepriseController::class, 'ajoute'])->setName('ajout-entreprise');
    $app->post('/ajout-entreprise', [EntrepriseController::class, 'ajoute'])->setName('ajout-entreprise');
    $app->get('/entreprise/edit/{idEntreprise:\d+}', [EntrepriseController::class, 'edit'])->setName('edit-entreprise');
    $app->get('/etudiants[/{page:\d+}]', [EtudiantController::class, 'liste'])->setName('liste-etudiant');
    $app->get('/ajout-etudiant', [EtudiantController::class, 'ajoute'])->setName('ajout-etudiant');
    $app->post('/ajout-etudiant', [EtudiantController::class, 'ajoute'])->setName('ajout-etudiant');
    $app->get('/connexion', [HomeController::class, 'connexion'])->setName('connexion');
    $app->post('/connexion', [HomeController::class, 'connexion'])->setName('connexion');
    $app->get('/offres[/{page:\d+}]', [OffreController::class, 'liste'])->setName('Offres');

    /*
    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
    */
};

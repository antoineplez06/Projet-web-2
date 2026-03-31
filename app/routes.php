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
use App\Application\Controller\WishlistController;
use App\Application\Controller\PiloteController;
use App\Application\Controller\CandidatureController;
use App\Application\Controller\CampusController;
use App\Application\Controller\ConnexionController;
use App\Application\Middleware\LoggedMiddleware;
use App\Application\Middleware\RoleCheckMiddleware;
use App\Application\Domain\Role;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $factory = $app->getContainer()->get(ResponseFactoryInterface::class);

    $app->get('/', [HomeController::class, 'home'])->setName('home');

    $app->group('/entreprise', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('/liste[/{page:\d+}]', [EntrepriseController::class, 'listeAdmin'])->setName('entreprises-admin');
        $group->get('/ajout', [EntrepriseController::class, 'ajoute'])->setName('ajout-entreprise');
        $group->post('/ajout', [EntrepriseController::class, 'ajoute'])->setName('ajout-entreprise');
        $group->get('/supprimer/{id}', [EntrepriseController::class, 'supprimer'])->setName('supprimer-entreprise');
        $group->post('/supprimer/{id}', [EntrepriseController::class, 'supprimer'])->setName('supprimer-entreprise');
        $group->get('/modifier/{id}', [EntrepriseController::class, 'modifier'])->setName('modifier-entreprise');
        $group->post('/modifier/{id}', [EntrepriseController::class, 'modifier'])->setName('modifier-entreprise');

    })->add(new RoleCheckMiddleware($factory, [Role::PILOTE, Role::ADMIN]));

    $app->group('/entreprise', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('[/{page:\d+}]', [EntrepriseController::class, 'liste'])->setName('liste-entreprises');
    })->add(new RoleCheckMiddleware($factory, [Role::ETUDIANT,Role::PILOTE, Role::ADMIN]));

    $app->get('/entreprises[/{page:\d+}]', [EntrepriseController::class, 'listean'])->setName('entreprises-an');
    $app->post('/entreprise/{id:[0-9]+}/noter', [EntrepriseController::class, 'noter'])->setName('entreprise-noter');

    $app->group('/campus', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('[/{page:\d+}]', [CampusController::class, 'listeAdmin'])->setName('campus-admin');
        $group->get('/ajout', [CampusController::class, 'ajoute'])->setName('ajout-campus');
        $group->post('/ajout', [CampusController::class, 'ajoute'])->setName('ajout-campus');
        $group->get('/supprimer/{id}', [CampusController::class, 'supprimer'])->setName('supprimer-campus');
        $group->post('/supprimer/{id}', [CampusController::class, 'supprimer'])->setName('supprimer-campus');
        $group->get('/modifier/{id}', [CampusController::class, 'modifier'])->setName('modifier-campus');
        $group->post('/modifier/{id}', [CampusController::class, 'modifier'])->setName('modifier-campus');

    })->add(new RoleCheckMiddleware($factory, [Role::ADMIN]));

    $app->group('/etudiant', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('/inscription', [EtudiantController::class, 'ajoute'])->setName('ajoute');
        $group->post('/inscription', [EtudiantController::class, 'ajoute']);
        $group->get('/liste', [EtudiantController::class, 'liste'])->setName('liste-etudiants');

        $group->get('/modifier/{id}', [EtudiantController::class, 'modifier'])->setName('modifier-etudiant');
        $group->post('/modifier/{id}', [EtudiantController::class, 'modifier']);
        $group->post('/supprimer/{id}', [EtudiantController::class, 'supprimer'])->setName('supprimer-etudiant');

    })->add(new RoleCheckMiddleware($factory, [Role::PILOTE, Role::ADMIN]));

    $app->get('/connexion', [ConnexionController::class, 'connexion'])->setName('connexion');
    $app->post('/connexion', [ConnexionController::class, 'connexion'])->setName('connexion');

    $app->group('/pilote', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('/ajout', [PiloteController::class, 'ajoute'])->setName('ajout-pilote');
        $group->post('/ajout', [PiloteController::class, 'ajoute'])->setName('ajout-pilote');
        $group->get('/liste', [PiloteController::class, 'liste'])->setName('liste-pilotes');
        $group->get('/modifier/{id}', [PiloteController::class, 'modifier'])->setName('modifier-pilote');
        $group->post('/modifier/{id}', [PiloteController::class, 'modifier'])->setName('modifier-pilote');
        $group->post('/supprimer/{id}', [PiloteController::class, 'supprimer'])->setName('supprimer-pilote');
    })->add(new RoleCheckMiddleware($factory, [Role::ADMIN]));

    $app->group('/offres', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('/admin[/{page:\d+}]', [OffreController::class, 'listeAdmin'])->setName('offres-admin');
        $group->get('/ajout', [OffreController::class, 'ajoute'])->setName('ajout-offre');
        $group->post('/ajout', [OffreController::class, 'ajoute'])->setName('ajout-offre');
        $group->get('/supprimer/{id}', [OffreController::class, 'supprimer'])->setName('supprimer-offre');
        $group->post('/supprimer/{id}', [OffreController::class, 'supprimer'])->setName('supprimer-offre');
        $group->get('/modifier/{id}', [OffreController::class, 'modifier'])->setName('modifier-offre');
        $group->post('/modifier/{id}', [OffreController::class, 'modifier'])->setName('modifier-offre');
    })->add(new RoleCheckMiddleware($factory, [Role::PILOTE, Role::ADMIN]));

    $app->group('/offres', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('[/{page:\d+}]', [OffreController::class, 'liste'])->setName('Offres');
        $group->get('/postuler/{id}', [OffreController::class, 'afficherFormulairePostuler'])->setName('page-postuler');

    })->add(new RoleCheckMiddleware($factory, [Role::ETUDIANT,Role::PILOTE, Role::ADMIN]));
    $app->get('/offres-an[/{page:\d+}]', [OffreController::class, 'listean'])->setName('offres-an');
    
    $app->group('/accueil', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('', [HomeController::class, 'accueilCo'])->setName('accueil-co');
    })->add(new RoleCheckMiddleware($factory, [Role::ETUDIANT,Role::PILOTE, Role::ADMIN]));

    $app->group('/wishlist', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('', [WishlistController::class, 'list'])->setName('wishlist');
        $group->get('/add/{idOffre}', [WishlistController::class, 'add'])->setName('app_wishlist_add');
        $group->get('/remove/{id}', [WishlistController::class, 'remove'])->setName('wishlist-remove');
    })->add(new RoleCheckMiddleware($factory, [Role::ETUDIANT,Role::PILOTE, Role::ADMIN]));

    $app->group('/deconnexion', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('', [HomeController::class, 'deconnexion'])->setName('deconnexion');
    })->add(new RoleCheckMiddleware($factory, [Role::ETUDIANT,Role::PILOTE, Role::ADMIN]));

    $app->group('/candidatures', function (RouteCollectorProxy $group) use ($factory) {
        $group->get('', [CandidatureController::class, 'list'])->setName('candidatures');
        $group->post('/enregistrer/{id}', [CandidatureController::class, 'add'])->setName('action-enregistrer-candidature');
    })->add(new RoleCheckMiddleware($factory, [Role::ETUDIANT,Role::PILOTE, Role::ADMIN]));

    $app->get('/debug-session', function(Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'session_id' => session_id(),
            'session_data' => $_SESSION
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
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
use App\Application\Controller\CandidatureController;
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
    $app->get('/entreprises[/{page:\d+}]', [EntrepriseController::class, 'liste'])->setName('liste-entreprises');
    $app->get('/entreprises-an[/{page:\d+}]', [EntrepriseController::class, 'listean'])->setName('entreprises-an');
    $app->get('/entreprises-admin[/{page:\d+}]', [EntrepriseController::class, 'listeAdmin'])->setName('entreprises-admin');
    $app->get('/ajout-entreprise', [EntrepriseController::class, 'ajoute'])->setName('ajout-entreprise');
    $app->post('/ajout-entreprise', [EntrepriseController::class, 'ajoute'])->setName('ajout-entreprise');
    $app->get('/entreprise/edit/{idEntreprise:\d+}', [EntrepriseController::class, 'edit'])->setName('edit-entreprise');
    $app->get('/supprimer-entreprise/{id}', [EntrepriseController::class, 'supprimer'])->setName('supprimer-entreprise');
    $app->post('/supprimer-entreprise/{id}', [EntrepriseController::class, 'supprimer'])->setName('supprimer-entreprise');
    $app->get('/modifier-entreprise/{id}', [EntrepriseController::class, 'modifier'])->setName('modifier-entreprise');
    $app->post('/modifier-entreprise/{id}', [EntrepriseController::class, 'modifier'])->setName('modifier-entreprise');

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
    $app->get('/offres[/{page:\d+}]', [OffreController::class, 'liste'])->setName('Offres');
    $app->get('/offres-an[/{page:\d+}]', [OffreController::class, 'listean'])->setName('offres-an');
    $app->get('/ajout-offre', [OffreController::class, 'ajoute'])->setName('ajout-offre');
    $app->post('/ajout-offre', [OffreController::class, 'ajoute'])->setName('ajout-offre');
    $app->get('/postuler/{id}', [OffreController::class, 'afficherFormulairePostuler'])->setName('page-postuler');
    $app->get('/accueil-co', [HomeController::class, 'accueilCo'])->setName('accueil-co');
    $app->get('/wishlist', [WishlistController::class, 'list'])->setName('wishlist');
    $app->get('/wishlist/add/{idOffre}', [WishlistController::class, 'add'])->setName('app_wishlist_add');
    $app->get('/wishlist/remove/{id}', [WishlistController::class, 'remove'])->setName('wishlist-remove');
    $app->get('/deconnexion', [HomeController::class, 'deconnexion'])->setName('deconnexion');
    $app->get('/candidatures', [CandidatureController::class, 'list'])->setName('candidatures');
    $app->post('/candidature/enregistrer/{id}', [CandidatureController::class, 'add'])->setName('action-enregistrer-candidature');

    $app->get('/debug-session', function(Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'session_id' => session_id(),
            'session_data' => $_SESSION
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
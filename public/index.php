<?php

declare(strict_types=1);

// Importation des classes nécessaires
use App\Application\ResponseEmitter\ResponseEmitter;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';

// 1. Initialisation du Container (PHP-DI)
$containerBuilder = new ContainerBuilder();

if (false) { // Passer à true en production pour le cache
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// 2. Chargement de la configuration (Settings, Dépendances, Repositories)
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

// 3. Construction du Container
$container = $containerBuilder->build();

// 4. Création de l'application Slim
AppFactory::setContainer($container);
$app = AppFactory::create();

// 5. Enregistrement des Middlewares et des Routes
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

// 6. Récupération des réglages d'erreurs depuis le container
/** @var SettingsInterface $settings */
$settings = $container->get(SettingsInterface::class);

$displayErrorDetails = $settings->get('displayErrorDetails');
$logError = $settings->get('logError');
$logErrorDetails = $settings->get('logErrorDetails');

// 7. Création de la requête
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// 8. Ajout des Middlewares standards de Slim
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

/**
 * GESTION DES ERREURS
 * On utilise le moteur natif de Slim pour éviter les erreurs "Class not found"
 */
$app->addErrorMiddleware($displayErrorDetails, $logError, $logErrorDetails);

// 9. Lancement de l'application
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
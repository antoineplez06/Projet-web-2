<?php

namespace App\Application\Controller;

use App\Application\Domain\Wishlist;
use App\Application\Domain\Offre;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class WishlistController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        
        // On récupère toutes les entrées de la wishlist
        $favoris = $this->entityManager->getRepository(Wishlist::class)->findAll();

        return $view->render($response, 'Wishlist-list.html.twig', [
            'favoris' => $favoris
        ]);
    }

    public function add(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $idOffre = (int)$args['idOffre'];
        
        // 1. On récupère l'objet Offre correspondant
        $offre = $this->entityManager->getRepository(Offre::class)->find($idOffre);

        if ($offre) {
            // 2. On crée l'entité Wishlist avec l'objet Offre
            $wishlist = new Wishlist($offre);
            $this->entityManager->persist($wishlist);
            $this->entityManager->flush();
        }

        return $response->withHeader('Location', '/wishlist')->withStatus(302);
    }

    public function remove(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    // On récupère l'ID de la ligne wishlist (id_wishlist)
    $id = (int)$args['id'];

    // On cherche l'élément dans la base
    $item = $this->entityManager->getRepository(Wishlist::class)->find($id);

    if ($item) {
        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

    // On redirige vers la page wishlist pour voir le changement
    return $response->withHeader('Location', '/wishlist')->withStatus(302);
}
}
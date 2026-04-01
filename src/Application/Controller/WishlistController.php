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
        $userConnecte = $request->getAttribute('user');

        $favoris = $this->entityManager->getRepository(Wishlist::class)->findBy([
            'etudiant' => $userConnecte,
        ]);

        return $view->render($response, 'favoris/liste.html.twig', [
            'favoris' => $favoris
        ]);
    }

    public function add(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $idOffre = (int)$args['idOffre'];
        $userConnecte = $request->getAttribute('user');
        $offre = $this->entityManager->getRepository(Offre::class)->find($idOffre);

        if ($offre && $userConnecte) {
            $dejaEnWishlist = $this->entityManager->getRepository(Wishlist::class)->findOneBy([
                'offre'    => $offre,
                'etudiant' => $userConnecte,
            ]);

            if (!$dejaEnWishlist) {
                $wishlist = new Wishlist($offre, $userConnecte);
                $this->entityManager->persist($wishlist);
                $this->entityManager->flush();
            }
        }

        return $response->withHeader('Location', '/wishlist')->withStatus(302);
    }
    public function remove(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int)$args['id'];

        $item = $this->entityManager->getRepository(Wishlist::class)->find($id);

        if ($item) {
            $this->entityManager->remove($item);
            $this->entityManager->flush();
        }

        return $response->withHeader('Location', '/wishlist')->withStatus(302);
    }
}

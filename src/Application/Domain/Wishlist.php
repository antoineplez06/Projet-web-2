<?php

namespace App\Application\Domain;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

#[Entity, Table(name: 'wishlist')]
class Wishlist
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'IDENTITY')]
    private int $id_wishlist;

    #[ManyToOne(targetEntity: Offre::class)]
    #[JoinColumn(name: 'idOffre', referencedColumnName: 'idOffre', nullable: false)]
    private Offre $offre;

    public function __construct(Offre $offre)
    {
        $this->offre = $offre;
    }

    public function getIdWishlist(): int 
    { 
        return $this->id_wishlist; 
    }

    public function getOffre(): Offre 
    { 
        return $this->offre; 
    }
}
<?php

namespace App\Application\Domain;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

#[Entity, Table(name: 'campus')]
class Campus
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'IDENTITY')]
    private int $id_campus;

    #[Column(type: 'string', length: 50)]
    private string $ville;


    public function __construct(
        string $ville
    ) {
        $this->ville = $ville;
    }


    public function getIdCampus(): int
    {
        return $this->id_campus;
    }
    public function getVille(): string
    {
        return $this->ville;
    }
}

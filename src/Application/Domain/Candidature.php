<?php

namespace App\Application\Domain;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use App\Application\Domain\user;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

#[Entity, Table(name: 'candidatures')]
class Candidature
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'IDENTITY')]
    private int $id_candidature;

    #[Column(type: 'string', length: 50)]
    private string $statut = 'En cours de traitement';

    #[ManyToOne(targetEntity: Offre::class)]
    #[JoinColumn(name: 'idOffre', referencedColumnName: 'idOffre', nullable: false)]
    private Offre $offre;

    #[ManyToOne(targetEntity: user::class)]
    #[JoinColumn(name: 'id_etudiant', referencedColumnName: 'id', nullable: false)]
    private user $etudiant;

    public function __construct(Offre $offre, user $etudiant)
    {
        $this->offre = $offre;
        $this->etudiant = $etudiant;
    }

    public function getIdCandidature(): int
    {
        return $this->id_candidature;
    }
    public function getStatut(): string
    {
        return $this->statut;
    }
    public function getOffre(): Offre
    {
        return $this->offre;
    }

    public function getEtudiant(): user
    {
        return $this->etudiant;
    }
}

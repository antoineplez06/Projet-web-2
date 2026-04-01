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

    #[Column(type: 'text', nullable: true)]
    private ?string $lettreMotivation = null;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $cv = null;

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

    public function getLettreMotivation(): ?string
    {
        return $this->lettreMotivation;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }


    public function setLettreMotivation(?string $lettre): void
    {
        $this->lettreMotivation = $lettre;
    }

    public function setCv(?string $cv): void
    {
        $this->cv = $cv;
    }
}

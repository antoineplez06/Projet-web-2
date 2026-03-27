<?php

namespace App\Application\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'offres')]
class Offre
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $idOffre;

    #[Column(type: 'string', length: 255)]
    private string $nom;

    #[Column(type: 'string', length: 100)]
    private string $duree;

    #[Column(type: 'integer')]
    private int $nombreEtudiantPostule = 0;

    #[Column(type: 'string', length: 255)]
    private string $exigenceEtude;

    #[Column(type: 'string', length: 255)]
    private string $entreprise;

    #[Column(type: 'date_immutable')]
    private DateTimeImmutable $date;

    #[Column(type: 'float')]
    private float $remuneration;

    #[Column(type: 'text')]
    private string $description;

    #[Column(type: 'string', length: 50)]
    private string $presentielOuDistanciel;

    public function __construct(
        string $nom,
        string $duree,
        string $exigenceEtude,
        string $entreprise,
        DateTimeImmutable $date,
        float $remuneration,
        string $description,
        string $presentielOuDistanciel
    ) {
        $this->nom = $nom;
        $this->duree = $duree;
        $this->exigenceEtude = $exigenceEtude;
        $this->entreprise = $entreprise;
        $this->date = $date;
        $this->remuneration = $remuneration;
        $this->description = $description;
        $this->presentielOuDistanciel = $presentielOuDistanciel;
        $this->nombreEtudiantPostule = 0; 
    }


    public function getIdOffre(): int
    {
        return $this->idOffre;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDuree(): string
    {
        return $this->duree;
    }

    public function getNombreEtudiantPostule(): int
    {
        return $this->nombreEtudiantPostule;
    }

    public function getExigenceEtude(): string
    {
        return $this->exigenceEtude;
    }

    public function getEntreprise(): string
    {
        return $this->entreprise;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getRemuneration(): float
    {
        return $this->remuneration;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPresentielOuDistanciel(): string
    {
        return $this->presentielOuDistanciel;
    }
}
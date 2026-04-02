<?php

namespace App\Application\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany; 
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\Common\Collections\ArrayCollection; 
use Doctrine\Common\Collections\Collection; 
use App\Application\Domain\Campus;
use App\Application\Domain\Candidature; 

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

    
    #[ManyToOne(targetEntity: Entreprise::class, inversedBy: 'offres')]
    #[JoinColumn(name: 'id_entreprise', referencedColumnName: 'id', nullable: false)]
    private Entreprise $entreprise;

    #[Column(type: 'date_immutable')]
    private DateTimeImmutable $date;

    #[Column(type: 'float')]
    private float $remuneration;

    #[Column(type: 'text')]
    private string $description;

    #[Column(type: 'string', length: 50)]
    private string $presentielOuDistanciel;

    #[ManyToOne(targetEntity: Campus::class)]
    #[JoinColumn(name: 'id_campus', referencedColumnName: 'id_campus', nullable: false)]
    private Campus $campus;

    #[OneToMany(mappedBy: 'offre', targetEntity: Candidature::class)]
    private Collection $candidatures;

    public function __construct(
        string $nom,
        string $duree,
        string $exigenceEtude,
        Entreprise $entreprise,
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

        $this->candidatures = new ArrayCollection();
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
        return $this->candidatures->count();
    }
    public function getExigenceEtude(): string
    {
        return $this->exigenceEtude;
    }

    public function getEntreprise(): Entreprise
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
    public function getCampus(): Campus
    {
        return $this->campus;
    }

    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }


    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }
    public function setDuree(string $duree): void
    {
        $this->duree = $duree;
    }
    public function setExigenceEtude(string $exigenceEtude): void
    {
        $this->exigenceEtude = $exigenceEtude;
    }
    public function setDate(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }
    public function setRemuneration(float $remuneration): void
    {
        $this->remuneration = $remuneration;
    }
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
    public function setPresentielOuDistanciel(string $presentielOuDistanciel): void
    {
        $this->presentielOuDistanciel = $presentielOuDistanciel;
    }
    public function setCampus(Campus $campus): void
    {
        $this->campus = $campus;
    }
    public function setEntreprise(Entreprise $entreprise): void
    {
        $this->entreprise = $entreprise;
    }
}

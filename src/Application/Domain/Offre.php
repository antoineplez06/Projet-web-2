<?php

namespace App\Application\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ManyToOne;    // AJOUTÉ
use Doctrine\ORM\Mapping\JoinColumn; // AJOUTÉ

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

    /**
     * RELATION : Plusieurs offres pour une seule entreprise
     * inversedBy="offres" doit correspondre au nom de la variable dans Entreprise.php
     */
    #[ManyToOne(targetEntity: Entreprise::class, inversedBy: 'offres')]
    #[JoinColumn(name: 'id_entreprise', referencedColumnName: 'id', nullable: false)]
    private Entreprise $entreprise; // CHANGÉ : de string à Entreprise (Objet)

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
        Entreprise $entreprise, // CHANGÉ : On passe l'objet Entreprise
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

    // --- GETTERS ---

    public function getIdOffre(): int { return $this->idOffre; }
    public function getNom(): string { return $this->nom; }
    public function getDuree(): string { return $this->duree; }
    public function getNombreEtudiantPostule(): int { return $this->nombreEtudiantPostule; }
    public function getExigenceEtude(): string { return $this->exigenceEtude; }

    /**
     * Retourne maintenant l'objet Entreprise complet
     */
    public function getEntreprise(): Entreprise 
    {
        return $this->entreprise;
    }

    public function getDate(): DateTimeImmutable { return $this->date; }
    public function getRemuneration(): float { return $this->remuneration; }
    public function getDescription(): string { return $this->description; }
    public function getPresentielOuDistanciel(): string { return $this->presentielOuDistanciel; }

    // --- SETTERS ---

    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setDuree(string $duree): void { $this->duree = $duree; }
    public function setExigenceEtude(string $exigenceEtude): void { $this->exigenceEtude = $exigenceEtude; }
    
    /**
     * Permet de lier l'offre à une entreprise
     */
    public function setEntreprise(Entreprise $entreprise): void 
    { 
        $this->entreprise = $entreprise; 
    }

    public function setDate(DateTimeImmutable $date): void { $this->date = $date; }
    public function setRemuneration(float $remuneration): void { $this->remuneration = $remuneration; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setPresentielOuDistanciel(string $presentielOuDistanciel): void { $this->presentielOuDistanciel = $presentielOuDistanciel; }
}
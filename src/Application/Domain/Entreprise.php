<?php

namespace App\Application\Domain;

use Doctrine\Common\Collections\ArrayCollection; 
use Doctrine\Common\Collections\Collection;      
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;             
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'entreprises')]
class Entreprise
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $nom;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $adresse;

    #[Column(type: 'string', length: 14, nullable: false)]
    private string $siret;

    #[Column(type: 'string', length: 100, nullable: false)]
    private string $domaine;

    #[Column(type: 'string', length: 100, nullable: false)]
    private string $taille;

    #[Column(type: 'float', nullable:true)]
    private float $note;

    #[OneToMany(mappedBy: 'entreprise', targetEntity: Offre::class)]
    private Collection $offres;

    public function __construct(
        string $nom,
        string $adresse,
        string $siret,
        string $domaine,
        string $taille,
        float $note = 0
    ) {
        $this->nom = $nom;
        $this->adresse = $adresse;
        $this->siret = $siret;
        $this->domaine = $domaine;
        $this->taille = $taille;
        $this->offres = new ArrayCollection(); 
        $this->note = $note;
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getAdresse(): string { return $this->adresse; }
    public function getSiret(): string { return $this->siret; }
    public function getDomaine(): string { return $this->domaine; }
    public function getTaille(): string { return $this->taille; }
    public function getNote(): float { return $this->note ?? 0; }
    public function getOffres(): Collection { return $this->offres; }


    public function setNom(string $nom): self { $this->nom = $nom; return $this; }
    public function setAdresse(string $adresse): self { $this->adresse = $adresse; return $this; }
    public function setSiret(string $siret): self { $this->siret = $siret; return $this; }
    public function setDomaine(string $domaine): self { $this->domaine = $domaine; return $this; }
    public function setTaille(string $taille): self { $this->taille = $taille; return $this; }
    public function setNote(float $note): self { 
        $this->note = $note; 
        return $this; 
    }
}